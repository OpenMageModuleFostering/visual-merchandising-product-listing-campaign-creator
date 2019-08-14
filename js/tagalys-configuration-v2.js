var tagalysManualSyncStarted = false;
var tagalysClientSideWorkCompleted = false;
var tagalysWaitingForTagalys = false;
var tagalysSyncing = false;
var tagalysLabelStartManualSync = 'Sync Manually';
var tagalysLabelNothingToSync = 'Nothing to sync';
var tagalysLabelSyncing = 'Manual sync in progress - do not close browser (click to pause)';
var tagalysLabelManualSyncWaitingForTagalys = 'Waiting for Tagalys. You can close the browser.';
var tagalysLabelManualSyncFinished = 'Finished syncing. You can close the browser.';

function tagalysInterval(fn, time) {
    var timer = false;
    this.start = function () {
        if (!this.isRunning())
            timer = setInterval(fn, time);
    };
    this.stop = function () {
        clearInterval(timer);
        timer = false;
    };
    this.isRunning = function () {
        return timer !== false;
    };
}
var tagalysStatusUpdateInterval = new tagalysInterval(tagalysGetAndUpdateSyncStatus, 5000);
var tagalysManualSyncIndicatorInterval = new tagalysInterval(tagalysUpdateManualSyncIndicator, 2000);
var tagalysManualSyncTimeout = false;

document.addEventListener("DOMContentLoaded", function (e) {
    tagalysGetAndUpdateSyncStatus();
});

// monitoring
var tagalysMonitorInterval = new tagalysInterval(tagalysMonitorTimers, 5000);
tagalysMonitorInterval.start();
function tagalysMonitorTimers() {
    if (tagalysSyncing) {
        tagalysManualSyncIndicatorInterval.start();
        if (tagalysManualSyncTimeout === false) {
            tagalysManualSyncTimeout = setTimeout(tagalysSyncManually, 100);
        }
    } else {
        tagalysManualSyncIndicatorInterval.stop();
        tagalysStatusUpdateInterval.start();
    }
}


function tagalysToggleManualSync() {
    if (tagalysSyncing) {
        tagalysStopManualSync();
    } else {
        tagalysStartManualSync();
    }
}
function tagalysStopManualSync() {
    tagalysSyncing = false;

    // stop timer
    if (tagalysManualSyncTimeout === false) {
        // not running. nothing to do.
    } else {
        clearTimeout(tagalysManualSyncTimeout);
        tagalysManualSyncTimeout = false;
    }
    tagalysManualSyncIndicatorInterval.stop();

    tagalysUpdateSyncButtonLabel();

    // start updates
    tagalysStatusUpdateInterval.start();
}
function tagalysStartManualSync() {
    // stop timer if running
    if (tagalysManualSyncTimeout === false) {
        // not running. nothing to do.
    } else {
        clearTimeout(tagalysManualSyncTimeout);
        tagalysManualSyncTimeout = false;
    }

    // stop updates
    tagalysStatusUpdateInterval.stop();

    // start manual sync
    tagalysSyncing = true;
    tagalysManualSyncTimeout = setTimeout(tagalysSyncManually, 100);
    tagalysManualSyncIndicatorInterval.start();
    tagalysUpdateSyncButtonLabel();
}
function tagalysUpdateSyncButtonLabel() {
    var toggleButton = document.getElementById('tagalys-toggle-manual-sync');
    if (tagalysSyncing) {
        toggleButton.innerHTML = tagalysLabelSyncing;
    } else {
        if (tagalysClientSideWorkCompleted) {
            if (tagalysWaitingForTagalys) {
                toggleButton.innerHTML = tagalysLabelManualSyncWaitingForTagalys;
            } else {
                if (tagalysManualSyncStarted) {
                    toggleButton.innerHTML = tagalysLabelManualSyncFinished;
                } else {
                    toggleButton.innerHTML = tagalysLabelNothingToSync;
                }
            }
        } else {
            toggleButton.innerHTML = tagalysLabelStartManualSync;
        }
    }
}
function tagalysUpdateManualSyncIndicator() {
    if (tagalysSyncing) {
        var toggleButton = document.getElementById('tagalys-toggle-manual-sync');
        var currentIndicator = toggleButton.innerHTML;
        var currentDotStr = currentIndicator.substr(tagalysLabelSyncing.length, 3);
        var nextDotStr = '...';
        switch(currentDotStr) {
            case '.':
                nextDotStr = '..';
                break;
            case '..':
                nextDotStr = '...';
                break;
            case '...':
                nextDotStr = '.';
                break;
        }
        toggleButton.innerHTML = tagalysLabelSyncing + nextDotStr;
    }
}
function tagalysSyncManually() {
    tagalysManualSyncStarted = true;
    if (tagalysSyncing) {
        new Ajax.Request(
            syncManuallyUrl,
            {
                method: 'GET',
                loaderArea: false,
                onSuccess: function(transport) {
                    var syncStatus = JSON.parse(transport.responseText);
                    updateSyncStatus(syncStatus);
                    if (syncStatus.client_side_work_completed == true) {
                        tagalysStopManualSync();
                    } else {
                        tagalysManualSyncTimeout = setTimeout(tagalysSyncManually, 100);
                    }
                },
                onFailure : function() {
                    tagalysManualSyncTimeout = false;
                }
            }
        );
    } else {
        // don't do anything
        tagalysManualSyncTimeout = false;
    }
}
function tagalysGetAndUpdateSyncStatus() {
    if (tagalysSyncing == false) {
        new Ajax.Request(
            syncStatusUrl,
            {
                method: 'GET',
                loaderArea: false,
                onSuccess: function(transport) {
                    var syncStatus = JSON.parse(transport.responseText);
                    updateSyncStatus(syncStatus);
                }
                // onFailure : failure
            }
        );
    }
}
function updateSyncStatus(syncStatus) {
    tagalysClientSideWorkCompleted = syncStatus.client_side_work_completed;
    if (syncStatus.waiting_for_tagalys == true) {
        tagalysWaitingForTagalys = true;
    } else {
        tagalysWaitingForTagalys = false;
    }
    tagalysUpdateSyncButtonLabel();
    document.getElementById('note_sync_status').innerHTML = syncStatus.status;
    for (store_id in syncStatus.stores) {
        document.getElementById('admin_tagalys_core_store_' + store_id + '_note_setup_complete').innerHTML = (syncStatus.stores[store_id].setup_complete ? 'Yes' : 'No');
        var toggleButton = document.getElementById('tagalys-toggle-manual-sync');
        if (toggleButton.innerHTML == 'Sync Now') {
            var feed_status = syncStatus.stores[store_id].feed_status;
        } else {
            var feed_status = syncStatus.stores[store_id].feed_status.replace('Waiting to write', 'Writing');
        }
        document.getElementById('admin_tagalys_core_store_' + store_id + '_note_feed_status').innerHTML = feed_status;
        document.getElementById('admin_tagalys_core_store_' + store_id + '_note_updates_status').innerHTML = syncStatus.stores[store_id].updates_status;
    }
}