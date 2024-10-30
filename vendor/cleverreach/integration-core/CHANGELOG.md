# Changelog
All notable changes to this project will be documented in this file.
Procedure for releasing new version is [here](https://logeecom.atlassian.net/wiki/spaces/CR/pages/181600257/CORE+library+versioning+workflow).

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/).

## [v3.7.2](https://github.com/cleverreach/logeecore/compare/v3.7.1...v3.7.2)
- Make BufferCheckTask non-archivable

## [v3.7.1](https://github.com/cleverreach/logeecore/compare/v3.7.0...v3.7.1)
- Fix issue with not confirmed newsletter contacts being transferred as active to CR profile
- Fix cleanup task not working

## [v3.7.0](https://github.com/cleverreach/logeecore/compare/v3.5.1...v3.7.0)
- Added events buffer

## [v3.5.1](https://github.com/cleverreach/logeecore/compare/v3.5.0...v3.5.1)
- Added force fail method for queue items

## [v3.5.0](https://github.com/cleverreach/logeecore/compare/v3.4.7...v3.5.0)
- **BREAKABLE** Archival of queue items
- Added prevention of event duplication
- Added FilterStrategy feature
- Modified CrateSegmentsTask

## [v3.4.7](https://github.com/cleverreach/logeecore/compare/v3.4.6...v3.4.7)
- Fix issue with OrderSync task executing infinite number of times in case when fatal error occurs

## [v3.4.6](https://github.com/cleverreach/logeecore/compare/v3.4.5...v3.4.6)
- Adjust FieldService for custom fields
- Reduce log level of curl error

## [v3.4.5](https://github.com/cleverreach/logeecore/compare/v3.4.4...v3.4.5)
- Add curl client version

## [v3.4.4](https://github.com/cleverreach/logeecore/compare/v3.4.3...v3.4.4)
- Changes from CRSET 28

## [v3.4.3](https://github.com/cleverreach/logeecore/compare/v3.4.2...v3.4.3)
 - Fixed issue when notifications are not supported in `InitialSyncCompletedListener`

## [v3.4.2](https://github.com/cleverreach/logeecore/compare/v3.4.1...v3.4.2)
 - Fixed last order date field on receiver.

## [v3.4.1](https://github.com/cleverreach/logeecore/compare/v3.4.0...v3.4.1)
 - Modified `AppStateContext::changeState` to skip same state transition.
 - Fixed birthday field on receiver.

## [v3.4.0](https://github.com/cleverreach/logeecore/compare/v3.3.5...v3.4.0)
- **BREAKABLE** Added `AppState` package. Every integration needs to adjust state settings based on the current state context.
- **BREAKABLE** Added `EnabledFieldsSetEvent` and `EnabledFieldsSetEventListener`.
- **BREAKABLE** Added `UnsetModifier`.
- **BREAKABLE** Added `FieldMapConfigService` interface and its implementation.
- **BREAKABLE** Added `FieldMapService` interface and its implementation.
- **BREAKABLE** Modified `AuthorizationService`, `Configuration`, `InitialSyncCompletedListener`, and `InitialSyncTaskEnqueuer` (this services are used as transition points of app state).
- **BREAKABLE** Modified `FieldService` interface and its implementation. Every integration which overrides core `FieldService` should be changed accordingly.
- **BREAKABLE** Modified `ReceiversExporter` task to use new `FieldService` and `FieldMapService`.
- **BREAKABLE** Modified `GroupService` interface and its implementation.
- **BREAKABLE** Modified `CreateGroupTask`.
- **BREAKABLE** Modified `CreateFieldsTask`.
- **BREAKABLE** Modified `SecondarySyncEnqueuer`.
- Modified `SecodarySyncTask` (`CreateFieldsTask` added as part of composite task).
- Modified form creation behaviour (`CreateDefaultFormTask` is changed).
- Modified Receiver DTO  (now custom attribute can be added).

## [v3.3.5](https://github.com/cleverreach/logeecore/compare/v3.3.4...v3.3.5)
- Add async request timeout in SupportService.

## [v3.3.4](https://github.com/cleverreach/logeecore/compare/v3.3.3...v3.3.4)
- Changed access modifiers from private to protected in ReceiverSyncTask and ReceiversExporter task

## [v3.3.3](https://github.com/cleverreach/logeecore/compare/v3.3.2...v3.3.3)
- Add `QueueService::countItems` function to count items in the provided status
- Add `QueueService::findExpiredRunningItems` function to return expired items in progress status
- Optimize `QueueService::failOrRequeueExpiredTasks` and `QueueService::startOldestQueuedItems` functions

## [v3.3.2](https://github.com/cleverreach/logeecore/compare/v3.3.1...v3.3.2)
- Add the more detail why AC email sending failed

## [v3.3.1](https://github.com/cleverreach/logeecore/compare/v3.3.0...v3.3.1)
- Fix setting scheduled time on AutomationRecord 
- Fix checking existing automation records 

## [v3.3.0](https://github.com/cleverreach/logeecore/compare/v3.2.7...v3.3.0)
- Add abandoned cart records feature
- **BREAKABLE** Added `AutomationRecordService::trigger` method
- **BREAKABLE** Extended `AutomationRecord` entity with additional indexed fields
- Refactor log level setting

## [v3.2.7](https://github.com/cleverreach/logeecore/compare/v3.2.6...v3.2.7)
- Remove diagnostics. This version is functionally identical to v3.2.2.

## [v3.2.6](https://github.com/cleverreach/logeecore/compare/v3.2.5...v3.2.6)
- Replace receiver export progress with keep alive signal.

## [v3.2.5](https://github.com/cleverreach/logeecore/compare/v3.2.4...v3.2.5)
- Report receiver export progress once every 60 seconds at most.

## [v3.2.4](https://github.com/cleverreach/logeecore/compare/v3.2.3...v3.2.4)
- Revert report progress changes.
- Change that only every 10 batches progress will be reproted during export.

## [v3.2.3](https://github.com/cleverreach/logeecore/compare/v3.2.2...v3.2.3)
- Change report progress frequency. 

## [v3.2.2](https://github.com/cleverreach/logeecore/compare/v3.2.1...v3.2.2)
- Make secondary sync task enqueuer integration extensible. 

## [v3.2.1](https://github.com/cleverreach/logeecore/compare/v3.2.0...v3.2.1)
- Fix queue item retrieval in the support console. 

## [v3.2.0](https://github.com/cleverreach/logeecore/compare/v3.1.5...v3.2.0)
- Multi-store abandoned cart feature

## [v3.1.5](https://github.com/cleverreach/logeecore/compare/v3.1.4...v3.1.5)
- Optimize double opt-in task to create inactive recipient instead of active one.
- Fix wrong exception parameters for form proxy.

## [v3.1.4](https://github.com/cleverreach/logeecore/compare/v3.1.3...v3.1.4)
- Fix `SendDoubleOptInEmailsTask` Serialization.

## [v3.1.3](https://github.com/cleverreach/logeecore/compare/v3.1.2...v3.1.3)
- Revert tag fix.

## [v3.1.2](https://github.com/cleverreach/logeecore/compare/v3.1.1...v3.1.2)
- Fix tag to string transformation.

## [v3.1.1](https://github.com/cleverreach/logeecore/compare/v3.1.0...v3.1.1)
- Remove email from base receiver merger.

## [v3.1.0](https://github.com/cleverreach/logeecore/compare/v3.0.23...v3.1.0)
- **BREAKABLE**: Add `getOrderSource` method in `OrderService`.

## [v3.0.23](https://github.com/cleverreach/logeecore/compare/v3.0.22...v3.0.23)
- Fix name of the `Segment` created from `Tag`.

## [v3.0.22](https://github.com/cleverreach/logeecore/compare/v3.0.21...v3.0.22)
- Fix formatting issue of non unicode characters in `Tag`.

## [v3.0.21](https://github.com/cleverreach/logeecore/compare/v3.0.20...v3.0.21)
- Fix json serialization of the `SubscribeReceiverTask`.

## [v3.0.20](https://github.com/cleverreach/logeecore/compare/v3.0.19...v3.0.19)
- Fix tag formatting.
- Remove usage of deprecated `md5` function.
- Change `getTrace` method to `getTraceAsString`.

## [v3.0.19](https://github.com/cleverreach/logeecore/compare/v3.0.18...v3.0.19)
- Skipped operation timeouted CURL error.

## [v3.0.18](https://github.com/cleverreach/logeecore/compare/v3.0.17...v3.0.18)
- Add option to delete abandoned cart webhooks.

## [v3.0.17](https://github.com/cleverreach/logeecore/compare/v3.0.16...v3.0.17)
- Add the limit to the TaskCleanupTask to avoid memory overflow issues in case of larger se of pending task for delete.

## [v3.0.16](https://github.com/cleverreach/logeecore/compare/v3.0.15...v3.0.16)
- Improve offline mode.
- Fix queue item priority order.

## [v3.0.15](https://github.com/cleverreach/logeecore/compare/v3.0.13...v3.0.15)
- Change access token lifetime to 7 days.

## [v3.0.13](https://github.com/cleverreach/logeecore/compare/v3.0.12...v3.0.13)
- Whitelist inactive receiver when double opt-in is used.

## [v3.0.12](https://github.com/cleverreach/logeecore/compare/v3.0.11...v3.0.12)
- Fix NPS free text survey

## [v3.0.11](https://github.com/cleverreach/logeecore/compare/v3.0.10...v3.0.11)
- Add CLASS_NAME constant

## [v3.0.10](https://github.com/cleverreach/logeecore/compare/v3.0.9...v3.0.10)
- Fix issues on PHP 5.3

## [v3.0.9](https://github.com/cleverreach/logeecore/compare/v3.0.8...v3.0.9)
- Revert suffix to the blacklisted emails

## [v3.0.8](https://github.com/cleverreach/logeecore/compare/v3.0.7...v3.0.8)
- Add Blacklist DTO (email, comment, stamp, isLocked) 
- Removed usage of suffix on the blacklisted email (suffix is now part of blacklist comment)
- Complete suffix needs to be defined in integration (`GroupService::getBlacklistedEmailsSuffix` method)

## [v3.0.7](https://github.com/cleverreach/logeecore/compare/v3.0.6...v3.0.7)
- Add time-stamp to order item DTO.

## [v3.0.6](https://github.com/cleverreach/logeecore/compare/v3.0.5...v3.0.6)
- Fix the issue with the undefined index when the form creation fails.
- Fix the issue with the unsubscribe of the non-existing receiver.

## [v3.0.5](https://github.com/cleverreach/logeecore/compare/v3.0.4...v3.0.5)
- Fix issue with the unnecessary token refresh.

## [v3.0.4](https://github.com/cleverreach/logeecore/compare/v3.0.3...v3.0.4)
- Fix weekly schedule calculation.

## [v3.0.3](https://github.com/cleverreach/logeecore/compare/v3.0.2...v3.0.3)
- Changed the dynamic content name pattern.
- Added google campaign name property to mailing settings dto.
- Expanded stats dto with additional properties.

## [v3.0.2](https://github.com/cleverreach/logeecore/compare/v3.0.1...v3.0.2)
- Fix support console user data.
