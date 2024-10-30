/**
 * CleverReach WooCommerce Integration.
 *
 * @package CleverReach
 */

(function () {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			const statuses = {
				inProgress: 'in_progress',
				completed: 'completed',
				failed: 'failed',
			};

			const statusCheckUrl = document.getElementById( 'crAdminStatusCheckUrl' ).value;
			const progressPanel  = document.getElementById( 'crProgressPanel' );
			const progressBar    = document.getElementById( 'crProgress' );
			const progressText   = document.getElementById( 'crProgressText' );

			function initialSyncCompleteHandler() {
				location.reload();
			}

			function checkStatus() {
				CleverReach.Ajax.get(
					statusCheckUrl,
					null,
					function (response) {
						if (response.taskStatuses) {
							setTasksProgress( response.taskStatuses );
						}

						if ((response.status !== statuses.completed) && (response.status !== statuses.failed)) {
							setTimeout( checkStatus, 250 );
						} else {
							initialSyncCompleteHandler();
						}
					},
					'json',
					true
				);

			}

			function setTasksProgress(taskStatuses) {
				let sum = 0;
				for (const taskName in taskStatuses) {
					if (taskStatuses.hasOwnProperty( taskName )) {
						sum += taskStatuses[taskName].progress;

						if (taskStatuses[taskName].status === statuses.inProgress) {
							document.getElementById( taskName ).classList.add( 'cr-progress-active' );
						} else {
							document.getElementById( taskName ).classList.remove( 'cr-progress-active' );
						}
					}
				}
				let progress           = Math.ceil( sum / 3 );
				progressBar.value      = progress;
				progressText.innerText = progress.toString();
			}

			if (progressPanel) {
				checkStatus();
			}
		}
	);
})();
