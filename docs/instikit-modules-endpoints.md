# InstiKit — API Endpoint Inventory (auto-generated)

Total endpoints: **1701** across **34** route groups. URIs are shown under the `/api/v1/app/` prefix. `apiResource` expanded to index/store/show/update/destroy.


## student  (265 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/student/config/attendance-types/pre-requisite` | student:config |
| GET | `/api/v1/app/student/config/attendance-types` | student:config |
| POST | `/api/v1/app/student/config/attendance-types` | student:config |
| GET | `/api/v1/app/student/config/attendance-types/{id}` | student:config |
| PUT|PATCH | `/api/v1/app/student/config/attendance-types/{id}` | student:config |
| DELETE | `/api/v1/app/student/config/attendance-types/{id}` | student:config |
| GET | `/api/v1/app/student/config/document-types` | student:config |
| POST | `/api/v1/app/student/config/document-types` | student:config |
| GET | `/api/v1/app/student/config/document-types/{id}` | student:config |
| PUT|PATCH | `/api/v1/app/student/config/document-types/{id}` | student:config |
| DELETE | `/api/v1/app/student/config/document-types/{id}` | student:config |
| GET | `/api/v1/app/student/registrations/{registration}/payment/pre-requisite` |  |
| POST | `/api/v1/app/student/registrations/{registration}/skip-payment` |  |
| POST | `/api/v1/app/student/registrations/{registration}/payment` |  |
| DELETE | `/api/v1/app/student/registrations/{registration}/payment/{uuid}` |  |
| POST | `/api/v1/app/student/registrations/{registration}/payment/initiate` |  |
| GET | `/api/v1/app/student/registrations/{registration}/assign-fee/pre-requisite` |  |
| POST | `/api/v1/app/student/registrations/{registration}/assign-fee` |  |
| POST | `/api/v1/app/student/registrations/{registration}/verify` |  |
| GET | `/api/v1/app/student/registrations/{registration}/action/pre-requisite` |  |
| POST | `/api/v1/app/student/registrations/{registration}/action` |  |
| POST | `/api/v1/app/student/registrations/{registration}/undo-reject` |  |
| GET | `/api/v1/app/student/registrations/{registration}/qualifications/pre-requisite` |  |
| POST | `/api/v1/app/student/registrations.qualifications` |  |
| GET | `/api/v1/app/student/registrations.qualifications/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/registrations.qualifications/{id}` |  |
| DELETE | `/api/v1/app/student/registrations.qualifications/{id}` |  |
| GET | `/api/v1/app/student/registrations/{registration}/documents/pre-requisite` |  |
| POST | `/api/v1/app/student/registrations.documents` |  |
| GET | `/api/v1/app/student/registrations.documents/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/registrations.documents/{id}` |  |
| DELETE | `/api/v1/app/student/registrations.documents/{id}` |  |
| POST | `/api/v1/app/student/registrations/{registration}/photo` |  |
| DELETE | `/api/v1/app/student/registrations/{registration}/photo` |  |
| GET | `/api/v1/app/student/registrations/pre-requisite` |  |
| POST | `/api/v1/app/student/registrations/delete` |  |
| POST | `/api/v1/app/student/registrations/assign` |  |
| POST | `/api/v1/app/student/registrations/stage` |  |
| POST | `/api/v1/app/student/registrations/{registration}/detail` |  |
| GET | `/api/v1/app/student/registrations/{registration}/guardians` |  |
| GET | `/api/v1/app/student/registrations/{registration}/qualifications` |  |
| GET | `/api/v1/app/student/registrations/{registration}/documents` |  |
| GET | `/api/v1/app/student/registrations` |  |
| POST | `/api/v1/app/student/registrations` |  |
| GET | `/api/v1/app/student/registrations/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/registrations/{id}` |  |
| DELETE | `/api/v1/app/student/registrations/{id}` |  |
| GET | `/api/v1/app/student/roll-number/pre-requisite` |  |
| GET | `/api/v1/app/student/roll-number/fetch` |  |
| POST | `/api/v1/app/student/roll-number` |  |
| GET | `/api/v1/app/student/photo/pre-requisite` |  |
| GET | `/api/v1/app/student/photo/fetch` |  |
| GET | `/api/v1/app/student/health-record/pre-requisite` |  |
| GET | `/api/v1/app/student/health-record/fetch` |  |
| POST | `/api/v1/app/student/health-record` |  |
| GET | `/api/v1/app/student/fee-allocation/pre-requisite` |  |
| GET | `/api/v1/app/student/fee-allocation/fetch` |  |
| POST | `/api/v1/app/student/fee-allocation` |  |
| POST | `/api/v1/app/student/fee-allocation/fee-concession` |  |
| POST | `/api/v1/app/student/fee-allocation/remove` |  |
| GET | `/api/v1/app/student/service-allocation/pre-requisite` |  |
| GET | `/api/v1/app/student/service-allocation/fetch` |  |
| POST | `/api/v1/app/student/service-allocation` |  |
| POST | `/api/v1/app/student/service-allocation/remove` |  |
| GET | `/api/v1/app/student/promotion/pre-requisite` |  |
| GET | `/api/v1/app/student/promotion/fetch` |  |
| POST | `/api/v1/app/student/promotion` |  |
| GET | `/api/v1/app/student/edit-requests/pre-requisite` |  |
| POST | `/api/v1/app/student/edit-requests/{edit_request}/action` |  |
| GET | `/api/v1/app/student/edit-requests` |  |
| GET | `/api/v1/app/student/edit-requests/{id}` |  |
| GET | `/api/v1/app/student/service-requests/pre-requisite` |  |
| POST | `/api/v1/app/student/service-requests/{service_request}/status` |  |
| GET | `/api/v1/app/student/service-requests` |  |
| POST | `/api/v1/app/student/service-requests` |  |
| GET | `/api/v1/app/student/service-requests/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/service-requests/{id}` |  |
| DELETE | `/api/v1/app/student/service-requests/{id}` |  |
| GET | `/api/v1/app/student/leave-requests/pre-requisite` |  |
| GET | `/api/v1/app/student/leave-requests` |  |
| POST | `/api/v1/app/student/leave-requests` |  |
| GET | `/api/v1/app/student/leave-requests/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/leave-requests/{id}` |  |
| DELETE | `/api/v1/app/student/leave-requests/{id}` |  |
| GET | `/api/v1/app/student/transfer-requests/pre-requisite` |  |
| POST | `/api/v1/app/student/transfer-requests/{transfer_request}/action` |  |
| GET | `/api/v1/app/student/transfer-requests` |  |
| POST | `/api/v1/app/student/transfer-requests` |  |
| GET | `/api/v1/app/student/transfer-requests/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/transfer-requests/{id}` |  |
| DELETE | `/api/v1/app/student/transfer-requests/{id}` |  |
| GET | `/api/v1/app/student/transfer/approval-requests/pre-requisite` |  |
| GET | `/api/v1/app/student/transfer/approval-requests` |  |
| GET | `/api/v1/app/student/transfers/pre-requisite` |  |
| POST | `/api/v1/app/student/transfers/{transfer}/media` |  |
| GET | `/api/v1/app/student/transfers` |  |
| POST | `/api/v1/app/student/transfers` |  |
| GET | `/api/v1/app/student/transfers/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/transfers/{id}` |  |
| DELETE | `/api/v1/app/student/transfers/{id}` |  |
| GET | `/api/v1/app/student/attendance/absentees/pre-requisite` |  |
| GET | `/api/v1/app/student/attendance/absentees` |  |
| GET | `/api/v1/app/student/attendance/pre-requisite` |  |
| GET | `/api/v1/app/student/attendance/fetch` |  |
| POST | `/api/v1/app/student/attendance/remove` |  |
| POST | `/api/v1/app/student/attendance/migrate` |  |
| POST | `/api/v1/app/student/attendance` |  |
| POST | `/api/v1/app/student/attendance/send-notification` |  |
| GET | `/api/v1/app/student/timesheet/check` |  |
| POST | `/api/v1/app/student/timesheet/clock` |  |
| GET | `/api/v1/app/student/timesheet/batch/pre-requisite` |  |
| GET | `/api/v1/app/student/timesheet/batch/fetch` |  |
| POST | `/api/v1/app/student/timesheet/batch` |  |
| GET | `/api/v1/app/student/timesheets` |  |
| POST | `/api/v1/app/student/timesheets` |  |
| GET | `/api/v1/app/student/timesheets/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/timesheets/{id}` |  |
| DELETE | `/api/v1/app/student/timesheets/{id}` |  |
| GET | `/api/v1/app/student/subject/pre-requisite` |  |
| GET | `/api/v1/app/student/subject/fetch` |  |
| POST | `/api/v1/app/student/subject` |  |
| GET | `/api/v1/app/student/documents/pre-requisite` |  |
| GET | `/api/v1/app/student/documents` |  |
| POST | `/api/v1/app/student/documents` |  |
| GET | `/api/v1/app/student/documents/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/documents/{id}` |  |
| DELETE | `/api/v1/app/student/documents/{id}` |  |
| POST | `/api/v1/app/student/documents/import` | student:edit |
| GET | `/api/v1/app/student/accounts/pre-requisite` |  |
| GET | `/api/v1/app/student/accounts` |  |
| POST | `/api/v1/app/student/accounts` |  |
| GET | `/api/v1/app/student/accounts/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/accounts/{id}` |  |
| DELETE | `/api/v1/app/student/accounts/{id}` |  |
| POST | `/api/v1/app/student/accounts/import` | student:edit |
| GET | `/api/v1/app/student/qualifications/pre-requisite` |  |
| GET | `/api/v1/app/student/qualifications` |  |
| POST | `/api/v1/app/student/qualifications` |  |
| GET | `/api/v1/app/student/qualifications/{id}` |  |
| PUT|PATCH | `/api/v1/app/student/qualifications/{id}` |  |
| DELETE | `/api/v1/app/student/qualifications/{id}` |  |
| POST | `/api/v1/app/student/qualifications/import` | student:edit |
| POST | `/api/v1/app/students/{student}/user/confirm` |  |
| GET | `/api/v1/app/students/{student}/user` |  |
| POST | `/api/v1/app/students/{student}/user` |  |
| PATCH | `/api/v1/app/students/{student}/user` |  |
| POST | `/api/v1/app/students/{student}/period` |  |
| POST | `/api/v1/app/students/{student}/photo` |  |
| DELETE | `/api/v1/app/students/{student}/photo` |  |
| DELETE | `/api/v1/app/students/{student}/admission` |  |
| DELETE | `/api/v1/app/students/{student}/promotion` |  |
| DELETE | `/api/v1/app/students/{student}/alumni` |  |
| POST | `/api/v1/app/students/{student}/default-period` |  |
| GET | `/api/v1/app/students/{student}/guardians/pre-requisite` |  |
| POST | `/api/v1/app/students/{student}/guardians/{guardian}/make-primary` |  |
| GET | `/api/v1/app/students.guardians` |  |
| POST | `/api/v1/app/students.guardians` |  |
| GET | `/api/v1/app/students.guardians/{id}` |  |
| PUT|PATCH | `/api/v1/app/students.guardians/{id}` |  |
| DELETE | `/api/v1/app/students.guardians/{id}` |  |
| GET | `/api/v1/app/students/{student}/siblings/pre-requisite` |  |
| GET | `/api/v1/app/students.siblings` |  |
| GET | `/api/v1/app/students/{student}/records/pre-requisite` |  |
| GET | `/api/v1/app/students.records` |  |
| POST | `/api/v1/app/students.records` |  |
| GET | `/api/v1/app/students.records/{id}` |  |
| PUT|PATCH | `/api/v1/app/students.records/{id}` |  |
| DELETE | `/api/v1/app/students.records/{id}` |  |
| GET | `/api/v1/app/students/{student}/fee/pre-requisite` |  |
| GET | `/api/v1/app/students/{student}/fee` |  |
| GET | `/api/v1/app/students/{student}/sibling-fees` |  |
| GET | `/api/v1/app/students/{student}/fee/list` |  |
| GET | `/api/v1/app/students/{student}/fee/summary` |  |
| GET | `/api/v1/app/students/{student}/fees` |  |
| POST | `/api/v1/app/students/{student}/fee` |  |
| PATCH | `/api/v1/app/students/{student}/fee` |  |
| DELETE | `/api/v1/app/students/{student}/fee` |  |
| POST | `/api/v1/app/students/{student}/fee/lock-unlock` |  |
| POST | `/api/v1/app/students/{student}/fee/custom-concession` |  |
| GET | `/api/v1/app/students/{student}/attendance` |  |
| GET | `/api/v1/app/students/{student}/exam-report` |  |
| GET | `/api/v1/app/students/{student}/subject` |  |
| POST | `/api/v1/app/students/{student}/subject` |  |
| GET | `/api/v1/app/students/{student}/payment/pre-requisite` |  |
| POST | `/api/v1/app/students/{student}/head-wise-payment` |  |
| POST | `/api/v1/app/students/{student}/multi-head-wise-payment` |  |
| POST | `/api/v1/app/students/{student}/bank-transfer` |  |
| POST | `/api/v1/app/students/{student}/bank-transfers/{uuid}/action` |  |
| POST | `/api/v1/app/students/{student}/payment` |  |
| POST | `/api/v1/app/students/{student}/payment/initiate` |  |
| GET | `/api/v1/app/students/{student}/payment/{uuid}` |  |
| PATCH | `/api/v1/app/students/{student}/payment/{uuid}` |  |
| POST | `/api/v1/app/students/{student}/cancel-payment/{uuid}` |  |
| POST | `/api/v1/app/students/{student}/online-payment/initiate` |  |
| POST | `/api/v1/app/students/{student}/online-payment/complete` |  |
| POST | `/api/v1/app/students/{student}/online-payment/fail` |  |
| POST | `/api/v1/app/students/{student}/online-payment/{uuid}/status` |  |
| POST | `/api/v1/app/students/{student}/online-payment/{uuid}/refresh-self-payment` |  |
| GET | `/api/v1/app/students/{student}/custom-fees/pre-requisite` |  |
| GET | `/api/v1/app/students.custom-fees` |  |
| POST | `/api/v1/app/students.custom-fees` |  |
| GET | `/api/v1/app/students.custom-fees/{id}` |  |
| PUT|PATCH | `/api/v1/app/students.custom-fees/{id}` |  |
| DELETE | `/api/v1/app/students.custom-fees/{id}` |  |
| GET | `/api/v1/app/students/{student}/fee-refunds/pre-requisite` |  |
| POST | `/api/v1/app/students/{student}/fee-refunds/{uuid}/cancel` |  |
| GET | `/api/v1/app/students.fee-refunds` |  |
| POST | `/api/v1/app/students.fee-refunds` |  |
| GET | `/api/v1/app/students.fee-refunds/{id}` |  |
| PUT|PATCH | `/api/v1/app/students.fee-refunds/{id}` |  |
| DELETE | `/api/v1/app/students.fee-refunds/{id}` |  |
| GET | `/api/v1/app/students/{student}/dialogues/pre-requisite` | student:dialogue |
| GET | `/api/v1/app/students.dialogues` | student:dialogue |
| POST | `/api/v1/app/students.dialogues` | student:dialogue |
| GET | `/api/v1/app/students.dialogues/{id}` | student:dialogue |
| PUT|PATCH | `/api/v1/app/students.dialogues/{id}` | student:dialogue |
| DELETE | `/api/v1/app/students.dialogues/{id}` | student:dialogue |
| GET | `/api/v1/app/students/{student}/accounts/pre-requisite` |  |
| GET | `/api/v1/app/students.accounts` |  |
| POST | `/api/v1/app/students.accounts` |  |
| GET | `/api/v1/app/students.accounts/{id}` |  |
| PUT|PATCH | `/api/v1/app/students.accounts/{id}` |  |
| DELETE | `/api/v1/app/students.accounts/{id}` |  |
| GET | `/api/v1/app/students/{student}/documents/pre-requisite` |  |
| GET | `/api/v1/app/students.documents` |  |
| POST | `/api/v1/app/students.documents` |  |
| GET | `/api/v1/app/students.documents/{id}` |  |
| PUT|PATCH | `/api/v1/app/students.documents/{id}` |  |
| DELETE | `/api/v1/app/students.documents/{id}` |  |
| GET | `/api/v1/app/students/{student}/qualifications/pre-requisite` |  |
| GET | `/api/v1/app/students.qualifications` |  |
| POST | `/api/v1/app/students.qualifications` |  |
| GET | `/api/v1/app/students.qualifications/{id}` |  |
| PUT|PATCH | `/api/v1/app/students.qualifications/{id}` |  |
| DELETE | `/api/v1/app/students.qualifications/{id}` |  |
| POST | `/api/v1/app/students/{student}/tags` |  |
| GET | `/api/v1/app/students/pre-requisite` |  |
| GET | `/api/v1/app/students/list` |  |
| GET | `/api/v1/app/students/list-all` |  |
| POST | `/api/v1/app/students/import` | students:create |
| GET | `/api/v1/app/students/import/history` | students:create |
| DELETE | `/api/v1/app/students/import/history/{uuid}` |  |
| POST | `/api/v1/app/students/custom-fee-import` | fee:set |
| GET | `/api/v1/app/students/{student}/edit-requests` |  |
| POST | `/api/v1/app/students/{student}/edit-requests` |  |
| GET | `/api/v1/app/students/{student}/edit-requests/{uuid}` |  |
| GET | `/api/v1/app/students/summary` |  |
| POST | `/api/v1/app/students/tags` |  |
| POST | `/api/v1/app/students/mentor` |  |
| POST | `/api/v1/app/students/enrollment-type` |  |
| POST | `/api/v1/app/students/enrollment-status` |  |
| POST | `/api/v1/app/students/groups` |  |
| GET | `/api/v1/app/students` |  |
| GET | `/api/v1/app/students/{id}` |  |
| PUT|PATCH | `/api/v1/app/students/{id}` |  |
| DELETE | `/api/v1/app/students/{id}` |  |
| GET | `/api/v1/app/student/reports/date-wise-attendance/pre-requisite` | student:list-attendance |
| GET | `/api/v1/app/student/reports/date-wise-attendance` | student:list-attendance |
| GET | `/api/v1/app/student/reports/batch-wise-attendance/pre-requisite` | student:list-attendance |
| GET | `/api/v1/app/student/reports/batch-wise-attendance` | student:list-attendance |
| GET | `/api/v1/app/student/reports/subject-wise-attendance/pre-requisite` | student:list-attendance |
| GET | `/api/v1/app/student/reports/subject-wise-attendance` | student:list-attendance |
| GET | `/api/v1/app/student/reports/subject-wise-student/pre-requisite` | student:list-attendance |
| GET | `/api/v1/app/student/reports/daily-access-report/pre-requisite` | student:list-attendance |
| GET | `/api/v1/app/student/reports/daily-access-report` | student:list-attendance |

## employee  (200 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/employee/config/document-types` | employee:config |
| POST | `/api/v1/app/employee/config/document-types` | employee:config |
| GET | `/api/v1/app/employee/config/document-types/{id}` | employee:config |
| PUT|PATCH | `/api/v1/app/employee/config/document-types/{id}` | employee:config |
| DELETE | `/api/v1/app/employee/config/document-types/{id}` | employee:config |
| GET | `/api/v1/app/employee/departments/pre-requisite` |  |
| POST | `/api/v1/app/employee/departments/import` | department:create |
| GET | `/api/v1/app/employee/departments` |  |
| POST | `/api/v1/app/employee/departments` |  |
| GET | `/api/v1/app/employee/departments/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/departments/{id}` |  |
| DELETE | `/api/v1/app/employee/departments/{id}` |  |
| GET | `/api/v1/app/employee/designations/pre-requisite` |  |
| POST | `/api/v1/app/employee/designations/import` | designation:create |
| GET | `/api/v1/app/employee/designations` |  |
| POST | `/api/v1/app/employee/designations` |  |
| GET | `/api/v1/app/employee/designations/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/designations/{id}` |  |
| DELETE | `/api/v1/app/employee/designations/{id}` |  |
| GET | `/api/v1/app/employee/leave/types/pre-requisite` | leave:config |
| GET | `/api/v1/app/employee/leave/types` | leave:config |
| POST | `/api/v1/app/employee/leave/types` | leave:config |
| GET | `/api/v1/app/employee/leave/types/{id}` | leave:config |
| PUT|PATCH | `/api/v1/app/employee/leave/types/{id}` | leave:config |
| DELETE | `/api/v1/app/employee/leave/types/{id}` | leave:config |
| GET | `/api/v1/app/employee/leave/allocations/pre-requisite` |  |
| GET | `/api/v1/app/employee/leave/allocations/{leave_allocation}/leave-requests` |  |
| GET | `/api/v1/app/employee/leave/allocations` |  |
| POST | `/api/v1/app/employee/leave/allocations` |  |
| GET | `/api/v1/app/employee/leave/allocations/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/leave/allocations/{id}` |  |
| DELETE | `/api/v1/app/employee/leave/allocations/{id}` |  |
| POST | `/api/v1/app/employee/leave/requests/{leave_request}/undo` |  |
| POST | `/api/v1/app/employee/leave/requests/{leave_request}/status` |  |
| GET | `/api/v1/app/employee/leave/requests/pre-requisite` |  |
| GET | `/api/v1/app/employee/leave/requests` |  |
| POST | `/api/v1/app/employee/leave/requests` |  |
| GET | `/api/v1/app/employee/leave/requests/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/leave/requests/{id}` |  |
| DELETE | `/api/v1/app/employee/leave/requests/{id}` |  |
| GET | `/api/v1/app/employee/attendance/types/pre-requisite` | attendance:config |
| GET | `/api/v1/app/employee/attendance/types` | attendance:config |
| POST | `/api/v1/app/employee/attendance/types` | attendance:config |
| GET | `/api/v1/app/employee/attendance/types/{id}` | attendance:config |
| PUT|PATCH | `/api/v1/app/employee/attendance/types/{id}` | attendance:config |
| DELETE | `/api/v1/app/employee/attendance/types/{id}` | attendance:config |
| GET | `/api/v1/app/employee/attendance/pre-requisite` |  |
| GET | `/api/v1/app/employee/attendance/list` |  |
| GET | `/api/v1/app/employee/attendance/fetch` |  |
| POST | `/api/v1/app/employee/attendance/mark` |  |
| GET | `/api/v1/app/employee/attendance/production` |  |
| POST | `/api/v1/app/employee/attendance/production` |  |
| GET | `/api/v1/app/employee/attendance/timesheet/check` |  |
| POST | `/api/v1/app/employee/attendance/timesheet/clock` |  |
| POST | `/api/v1/app/employee/attendance/timesheet/sync` | timesheet:sync |
| POST | `/api/v1/app/employee/attendance/timesheets/import` | timesheet:import |
| GET | `/api/v1/app/employee/attendance/timesheets` |  |
| POST | `/api/v1/app/employee/attendance/timesheets` |  |
| GET | `/api/v1/app/employee/attendance/timesheets/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/attendance/timesheets/{id}` |  |
| DELETE | `/api/v1/app/employee/attendance/timesheets/{id}` |  |
| GET | `/api/v1/app/employee/attendance/work-shift/assign/pre-requisite` |  |
| GET | `/api/v1/app/employee/attendance/work-shift/assign/fetch` |  |
| POST | `/api/v1/app/employee/attendance/work-shift/assign` |  |
| GET | `/api/v1/app/employee/attendance/work-shifts/pre-requisite` |  |
| GET | `/api/v1/app/employee/attendance/work-shifts` |  |
| POST | `/api/v1/app/employee/attendance/work-shifts` |  |
| GET | `/api/v1/app/employee/attendance/work-shifts/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/attendance/work-shifts/{id}` |  |
| DELETE | `/api/v1/app/employee/attendance/work-shifts/{id}` |  |
| GET | `/api/v1/app/employee/payroll/pay-heads/pre-requisite` | payroll:config |
| POST | `/api/v1/app/employee/payroll/pay-heads/reorder` | payroll:config |
| GET | `/api/v1/app/employee/payroll/pay-heads` | payroll:config |
| POST | `/api/v1/app/employee/payroll/pay-heads` | payroll:config |
| GET | `/api/v1/app/employee/payroll/pay-heads/{id}` | payroll:config |
| PUT|PATCH | `/api/v1/app/employee/payroll/pay-heads/{id}` | payroll:config |
| DELETE | `/api/v1/app/employee/payroll/pay-heads/{id}` | payroll:config |
| GET | `/api/v1/app/employee/payroll/salary-templates/pre-requisite` |  |
| GET | `/api/v1/app/employee/payroll/salary-templates` |  |
| POST | `/api/v1/app/employee/payroll/salary-templates` |  |
| GET | `/api/v1/app/employee/payroll/salary-templates/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/payroll/salary-templates/{id}` |  |
| DELETE | `/api/v1/app/employee/payroll/salary-templates/{id}` |  |
| GET | `/api/v1/app/employee/payroll/salary-structures/pre-requisite` |  |
| GET | `/api/v1/app/employee/payroll/salary-structures` |  |
| POST | `/api/v1/app/employee/payroll/salary-structures` |  |
| GET | `/api/v1/app/employee/payroll/salary-structures/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/payroll/salary-structures/{id}` |  |
| DELETE | `/api/v1/app/employee/payroll/salary-structures/{id}` |  |
| GET | `/api/v1/app/employee/payrolls/fetch` |  |
| POST | `/api/v1/app/employee/payrolls/process` |  |
| POST | `/api/v1/app/employee/payrolls/{payroll}/process` |  |
| GET | `/api/v1/app/employee/payrolls/pre-requisite` |  |
| POST | `/api/v1/app/employee/payrolls/delete` |  |
| GET | `/api/v1/app/employee/payrolls` |  |
| POST | `/api/v1/app/employee/payrolls` |  |
| GET | `/api/v1/app/employee/payrolls/{id}` |  |
| PUT|PATCH | `/api/v1/app/employee/payrolls/{id}` |  |
| DELETE | `/api/v1/app/employee/payrolls/{id}` |  |
| GET | `/api/v1/app/employee/edit-requests/pre-requisite` |  |
| POST | `/api/v1/app/employee/edit-requests/{edit_request}/action` |  |
| GET | `/api/v1/app/employee/edit-requests` |  |
| GET | `/api/v1/app/employee/edit-requests/{id}` |  |
| GET | `/api/v1/app/employee/documents/pre-requisite` | employee:read |
| GET | `/api/v1/app/employee/documents` | employee:read |
| POST | `/api/v1/app/employee/documents` | employee:read |
| GET | `/api/v1/app/employee/documents/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employee/documents/{id}` | employee:read |
| DELETE | `/api/v1/app/employee/documents/{id}` | employee:read |
| POST | `/api/v1/app/employee/documents/import` | employee:edit |
| GET | `/api/v1/app/employee/accounts/pre-requisite` | employee:read |
| GET | `/api/v1/app/employee/accounts` | employee:read |
| POST | `/api/v1/app/employee/accounts` | employee:read |
| GET | `/api/v1/app/employee/accounts/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employee/accounts/{id}` | employee:read |
| DELETE | `/api/v1/app/employee/accounts/{id}` | employee:read |
| POST | `/api/v1/app/employee/accounts/import` | employee:edit |
| GET | `/api/v1/app/employee/qualifications/pre-requisite` | employee:read |
| GET | `/api/v1/app/employee/qualifications` | employee:read |
| POST | `/api/v1/app/employee/qualifications` | employee:read |
| GET | `/api/v1/app/employee/qualifications/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employee/qualifications/{id}` | employee:read |
| DELETE | `/api/v1/app/employee/qualifications/{id}` | employee:read |
| POST | `/api/v1/app/employee/qualifications/import` | employee:edit |
| GET | `/api/v1/app/employee/experiences/pre-requisite` | employee:read |
| GET | `/api/v1/app/employee/experiences` | employee:read |
| POST | `/api/v1/app/employee/experiences` | employee:read |
| GET | `/api/v1/app/employee/experiences/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employee/experiences/{id}` | employee:read |
| DELETE | `/api/v1/app/employee/experiences/{id}` | employee:read |
| POST | `/api/v1/app/employee/experiences/import` | employee:edit |
| POST | `/api/v1/app/employees/{employee}/user/confirm` | employee:read |
| GET | `/api/v1/app/employees/{employee}/user` | employee:read |
| POST | `/api/v1/app/employees/{employee}/user` | employee:read |
| PATCH | `/api/v1/app/employees/{employee}/user` | employee:read |
| POST | `/api/v1/app/employees/{employee}/period` | employee:read |
| POST | `/api/v1/app/employees/{employee}/photo` | employee:read |
| DELETE | `/api/v1/app/employees/{employee}/photo` | employee:read |
| GET | `/api/v1/app/employees/{employee}/records/pre-requisite` | employee:read |
| GET | `/api/v1/app/employees.records` | employee:read |
| POST | `/api/v1/app/employees.records` | employee:read |
| GET | `/api/v1/app/employees.records/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employees.records/{id}` | employee:read |
| DELETE | `/api/v1/app/employees.records/{id}` | employee:read |
| GET | `/api/v1/app/employees.incharges` | employee:read |
| GET | `/api/v1/app/employees/{employee}/work-shifts/pre-requisite` | employee:read |
| GET | `/api/v1/app/employees.work-shifts` | employee:read |
| POST | `/api/v1/app/employees.work-shifts` | employee:read |
| GET | `/api/v1/app/employees.work-shifts/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employees.work-shifts/{id}` | employee:read |
| DELETE | `/api/v1/app/employees.work-shifts/{id}` | employee:read |
| GET | `/api/v1/app/employees/{employee}/qualifications/pre-requisite` | employee:read |
| POST | `/api/v1/app/employees/{employee}/qualifications/{qualification}/action` | employee:read |
| GET | `/api/v1/app/employees.qualifications` | employee:read |
| POST | `/api/v1/app/employees.qualifications` | employee:read |
| GET | `/api/v1/app/employees.qualifications/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employees.qualifications/{id}` | employee:read |
| DELETE | `/api/v1/app/employees.qualifications/{id}` | employee:read |
| GET | `/api/v1/app/employees/{employee}/dialogues/pre-requisite` | employee:dialogue |
| GET | `/api/v1/app/employees.dialogues` | employee:dialogue |
| POST | `/api/v1/app/employees.dialogues` | employee:dialogue |
| GET | `/api/v1/app/employees.dialogues/{id}` | employee:dialogue |
| PUT|PATCH | `/api/v1/app/employees.dialogues/{id}` | employee:dialogue |
| DELETE | `/api/v1/app/employees.dialogues/{id}` | employee:dialogue |
| GET | `/api/v1/app/employees/{employee}/accounts/pre-requisite` | employee:read |
| POST | `/api/v1/app/employees/{employee}/accounts/{account}/action` | employee:read |
| POST | `/api/v1/app/employees/{employee}/accounts/{account}/make-primary` | employee:read |
| GET | `/api/v1/app/employees.accounts` | employee:read |
| POST | `/api/v1/app/employees.accounts` | employee:read |
| GET | `/api/v1/app/employees.accounts/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employees.accounts/{id}` | employee:read |
| DELETE | `/api/v1/app/employees.accounts/{id}` | employee:read |
| GET | `/api/v1/app/employees/{employee}/documents/pre-requisite` | employee:read |
| POST | `/api/v1/app/employees/{employee}/documents/{document}/action` | employee:read |
| GET | `/api/v1/app/employees.documents` | employee:read |
| POST | `/api/v1/app/employees.documents` | employee:read |
| GET | `/api/v1/app/employees.documents/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employees.documents/{id}` | employee:read |
| DELETE | `/api/v1/app/employees.documents/{id}` | employee:read |
| GET | `/api/v1/app/employees/{employee}/experiences/pre-requisite` | employee:read |
| POST | `/api/v1/app/employees/{employee}/experiences/{experience}/action` | employee:read |
| GET | `/api/v1/app/employees.experiences` | employee:read |
| POST | `/api/v1/app/employees.experiences` | employee:read |
| GET | `/api/v1/app/employees.experiences/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employees.experiences/{id}` | employee:read |
| DELETE | `/api/v1/app/employees.experiences/{id}` | employee:read |
| POST | `/api/v1/app/employees/{employee}/tags` | employee:read |
| GET | `/api/v1/app/employees/pre-requisite` | employee:read |
| GET | `/api/v1/app/employees/list` | employee:read |
| POST | `/api/v1/app/employees/import` | employee:create |
| GET | `/api/v1/app/employees/{employee}/edit-requests` | employee:read |
| POST | `/api/v1/app/employees/{employee}/edit-requests` | employee:read |
| GET | `/api/v1/app/employees/{employee}/edit-requests/{uuid}` | employee:read |
| POST | `/api/v1/app/employees/tags` | employee:read |
| POST | `/api/v1/app/employees/groups` | employee:read |
| GET | `/api/v1/app/employees` | employee:read |
| POST | `/api/v1/app/employees` | employee:read |
| GET | `/api/v1/app/employees/{id}` | employee:read |
| PUT|PATCH | `/api/v1/app/employees/{id}` | employee:read |
| DELETE | `/api/v1/app/employees/{id}` | employee:read |

## academic  (167 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/academic/departments/pre-requisite` | academic-department:manage |
| POST | `/api/v1/app/academic/departments/reorder` | academic-department:manage |
| GET | `/api/v1/app/academic/department-incharges/pre-requisite` | academic-department:manage |
| GET | `/api/v1/app/academic/department-incharges` | academic-department:manage |
| POST | `/api/v1/app/academic/department-incharges` | academic-department:manage |
| GET | `/api/v1/app/academic/department-incharges/{id}` | academic-department:manage |
| PUT|PATCH | `/api/v1/app/academic/department-incharges/{id}` | academic-department:manage |
| DELETE | `/api/v1/app/academic/department-incharges/{id}` | academic-department:manage |
| GET | `/api/v1/app/academic/departments` | academic-department:manage |
| POST | `/api/v1/app/academic/departments` | academic-department:manage |
| GET | `/api/v1/app/academic/departments/{id}` | academic-department:manage |
| PUT|PATCH | `/api/v1/app/academic/departments/{id}` | academic-department:manage |
| DELETE | `/api/v1/app/academic/departments/{id}` | academic-department:manage |
| GET | `/api/v1/app/academic/program-types/pre-requisite` | program:manage |
| GET | `/api/v1/app/academic/program-types` | program:manage |
| POST | `/api/v1/app/academic/program-types` | program:manage |
| GET | `/api/v1/app/academic/program-types/{id}` | program:manage |
| PUT|PATCH | `/api/v1/app/academic/program-types/{id}` | program:manage |
| DELETE | `/api/v1/app/academic/program-types/{id}` | program:manage |
| GET | `/api/v1/app/academic/programs/pre-requisite` | program:manage |
| POST | `/api/v1/app/academic/programs/reorder` | program:manage |
| GET | `/api/v1/app/academic/program-incharges/pre-requisite` | program:manage |
| GET | `/api/v1/app/academic/program-incharges` | program:manage |
| POST | `/api/v1/app/academic/program-incharges` | program:manage |
| GET | `/api/v1/app/academic/program-incharges/{id}` | program:manage |
| PUT|PATCH | `/api/v1/app/academic/program-incharges/{id}` | program:manage |
| DELETE | `/api/v1/app/academic/program-incharges/{id}` | program:manage |
| GET | `/api/v1/app/academic/programs` | program:manage |
| POST | `/api/v1/app/academic/programs` | program:manage |
| GET | `/api/v1/app/academic/programs/{id}` | program:manage |
| PUT|PATCH | `/api/v1/app/academic/programs/{id}` | program:manage |
| DELETE | `/api/v1/app/academic/programs/{id}` | program:manage |
| GET | `/api/v1/app/academic/sessions/pre-requisite` | session:manage |
| GET | `/api/v1/app/academic/sessions` | session:manage |
| POST | `/api/v1/app/academic/sessions` | session:manage |
| GET | `/api/v1/app/academic/sessions/{id}` | session:manage |
| PUT|PATCH | `/api/v1/app/academic/sessions/{id}` | session:manage |
| DELETE | `/api/v1/app/academic/sessions/{id}` | session:manage |
| GET | `/api/v1/app/academic/periods/pre-requisite` |  |
| POST | `/api/v1/app/academic/periods/{period}/select` | period:change |
| POST | `/api/v1/app/academic/periods/{period}/default` | period:update |
| POST | `/api/v1/app/academic/periods/{period}/archive` |  |
| POST | `/api/v1/app/academic/periods/{period}/unarchive` |  |
| POST | `/api/v1/app/academic/periods/{period}/import` | period:create |
| GET | `/api/v1/app/academic/periods` |  |
| POST | `/api/v1/app/academic/periods` |  |
| GET | `/api/v1/app/academic/periods/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/periods/{id}` |  |
| DELETE | `/api/v1/app/academic/periods/{id}` |  |
| GET | `/api/v1/app/academic/divisions/pre-requisite` |  |
| POST | `/api/v1/app/academic/divisions/reorder` |  |
| POST | `/api/v1/app/academic/divisions/{division}/config` |  |
| POST | `/api/v1/app/academic/divisions/{division}/period` |  |
| GET | `/api/v1/app/academic/divisions` |  |
| POST | `/api/v1/app/academic/divisions` |  |
| GET | `/api/v1/app/academic/divisions/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/divisions/{id}` |  |
| DELETE | `/api/v1/app/academic/divisions/{id}` |  |
| GET | `/api/v1/app/academic/division-incharges/pre-requisite` |  |
| GET | `/api/v1/app/academic/division-incharges` |  |
| POST | `/api/v1/app/academic/division-incharges` |  |
| GET | `/api/v1/app/academic/division-incharges/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/division-incharges/{id}` |  |
| DELETE | `/api/v1/app/academic/division-incharges/{id}` |  |
| GET | `/api/v1/app/academic/courses/pre-requisite` |  |
| POST | `/api/v1/app/academic/courses/{course}/batches` |  |
| POST | `/api/v1/app/academic/courses/reorder` |  |
| POST | `/api/v1/app/academic/courses/reorder-batch` |  |
| POST | `/api/v1/app/academic/courses/{course}/enrollment-seat` |  |
| POST | `/api/v1/app/academic/courses/{course}/config` |  |
| POST | `/api/v1/app/academic/courses/{course}/period` |  |
| POST | `/api/v1/app/academic/courses/import` | course:create |
| GET | `/api/v1/app/academic/courses` |  |
| POST | `/api/v1/app/academic/courses` |  |
| GET | `/api/v1/app/academic/courses/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/courses/{id}` |  |
| DELETE | `/api/v1/app/academic/courses/{id}` |  |
| GET | `/api/v1/app/academic/course-incharges/pre-requisite` |  |
| GET | `/api/v1/app/academic/course-incharges` |  |
| POST | `/api/v1/app/academic/course-incharges` |  |
| GET | `/api/v1/app/academic/course-incharges/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/course-incharges/{id}` |  |
| DELETE | `/api/v1/app/academic/course-incharges/{id}` |  |
| GET | `/api/v1/app/academic/enrollment-seats/pre-requisite` |  |
| GET | `/api/v1/app/academic/enrollment-seats` |  |
| POST | `/api/v1/app/academic/enrollment-seats` |  |
| GET | `/api/v1/app/academic/enrollment-seats/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/enrollment-seats/{id}` |  |
| DELETE | `/api/v1/app/academic/enrollment-seats/{id}` |  |
| GET | `/api/v1/app/academic/batches/pre-requisite` |  |
| GET | `/api/v1/app/academic/batches/{batch}/subjects` |  |
| POST | `/api/v1/app/academic/batches/{batch}/config` |  |
| POST | `/api/v1/app/academic/batches/{batch}/period` |  |
| POST | `/api/v1/app/academic/batches/import` | batch:create |
| GET | `/api/v1/app/academic/batches/{batch}/optional-fee-heads` |  |
| GET | `/api/v1/app/academic/batches` |  |
| POST | `/api/v1/app/academic/batches` |  |
| GET | `/api/v1/app/academic/batches/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/batches/{id}` |  |
| DELETE | `/api/v1/app/academic/batches/{id}` |  |
| GET | `/api/v1/app/academic/batch-incharges/pre-requisite` |  |
| GET | `/api/v1/app/academic/batch-incharges` |  |
| POST | `/api/v1/app/academic/batch-incharges` |  |
| GET | `/api/v1/app/academic/batch-incharges/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/batch-incharges/{id}` |  |
| DELETE | `/api/v1/app/academic/batch-incharges/{id}` |  |
| GET | `/api/v1/app/academic/subjects/pre-requisite` |  |
| POST | `/api/v1/app/academic/subjects/reorder` |  |
| GET | `/api/v1/app/academic/subjects.records` |  |
| POST | `/api/v1/app/academic/subjects.records` |  |
| GET | `/api/v1/app/academic/subjects.records/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/subjects.records/{id}` |  |
| DELETE | `/api/v1/app/academic/subjects.records/{id}` |  |
| POST | `/api/v1/app/academic/subjects/{subject}/fee` |  |
| GET | `/api/v1/app/academic/subjects` |  |
| POST | `/api/v1/app/academic/subjects` |  |
| GET | `/api/v1/app/academic/subjects/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/subjects/{id}` |  |
| DELETE | `/api/v1/app/academic/subjects/{id}` |  |
| GET | `/api/v1/app/academic/book-lists/pre-requisite` |  |
| POST | `/api/v1/app/academic/book-lists/import` | book-list:create |
| GET | `/api/v1/app/academic/book-lists` |  |
| POST | `/api/v1/app/academic/book-lists` |  |
| GET | `/api/v1/app/academic/book-lists/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/book-lists/{id}` |  |
| DELETE | `/api/v1/app/academic/book-lists/{id}` |  |
| GET | `/api/v1/app/academic/subject-incharges/pre-requisite` |  |
| POST | `/api/v1/app/academic/subject-incharges/import` | subject-incharge:create |
| GET | `/api/v1/app/academic/subject-incharges` |  |
| POST | `/api/v1/app/academic/subject-incharges` |  |
| GET | `/api/v1/app/academic/subject-incharges/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/subject-incharges/{id}` |  |
| DELETE | `/api/v1/app/academic/subject-incharges/{id}` |  |
| GET | `/api/v1/app/academic/certificate-templates/pre-requisite` |  |
| GET | `/api/v1/app/academic/certificate-templates` |  |
| POST | `/api/v1/app/academic/certificate-templates` |  |
| GET | `/api/v1/app/academic/certificate-templates/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/certificate-templates/{id}` |  |
| DELETE | `/api/v1/app/academic/certificate-templates/{id}` |  |
| GET | `/api/v1/app/academic/certificates/pre-requisite` |  |
| GET | `/api/v1/app/academic/certificates` |  |
| POST | `/api/v1/app/academic/certificates` |  |
| GET | `/api/v1/app/academic/certificates/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/certificates/{id}` |  |
| DELETE | `/api/v1/app/academic/certificates/{id}` |  |
| GET | `/api/v1/app/academic/id-card-templates/pre-requisite` |  |
| GET | `/api/v1/app/academic/id-card-templates` |  |
| POST | `/api/v1/app/academic/id-card-templates` |  |
| GET | `/api/v1/app/academic/id-card-templates/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/id-card-templates/{id}` |  |
| DELETE | `/api/v1/app/academic/id-card-templates/{id}` |  |
| GET | `/api/v1/app/academic/id-cards/pre-requisite` |  |
| GET | `/api/v1/app/academic/id-cards` |  |
| GET | `/api/v1/app/academic/class-timings/pre-requisite` |  |
| GET | `/api/v1/app/academic/class-timings` |  |
| POST | `/api/v1/app/academic/class-timings` |  |
| GET | `/api/v1/app/academic/class-timings/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/class-timings/{id}` |  |
| DELETE | `/api/v1/app/academic/class-timings/{id}` |  |
| GET | `/api/v1/app/academic/timetables/pre-requisite` |  |
| GET | `/api/v1/app/academic/timetables/{timetable}/allocation/pre-requisite` | timetable:allocate |
| POST | `/api/v1/app/academic/timetables/{timetable}/allocation` | timetable:allocate |
| GET | `/api/v1/app/academic/timetables` |  |
| POST | `/api/v1/app/academic/timetables` |  |
| GET | `/api/v1/app/academic/timetables/{id}` |  |
| PUT|PATCH | `/api/v1/app/academic/timetables/{id}` |  |
| DELETE | `/api/v1/app/academic/timetables/{id}` |  |

## core  (132 endpoints)

| Method | URI | Permission |
|---|---|---|
| POST | `/api/v1/app/support/token` |  |
| GET | `/api/v1/app/organizations` | organization:manage |
| POST | `/api/v1/app/organizations` | organization:manage |
| GET | `/api/v1/app/organizations/{id}` | organization:manage |
| PUT|PATCH | `/api/v1/app/organizations/{id}` | organization:manage |
| DELETE | `/api/v1/app/organizations/{id}` | organization:manage |
| GET | `/api/v1/app/teams` | team:manage |
| GET | `/api/v1/app/teams/{id}` | team:manage |
| GET | `/api/v1/app/teams.roles` | team:manage |
| POST | `/api/v1/app/teams.roles` | team:manage |
| GET | `/api/v1/app/teams.roles/{id}` | team:manage |
| DELETE | `/api/v1/app/teams.roles/{id}` | team:manage |
| GET | `/api/v1/app/teams/{team}/config` | team:manage |
| POST | `/api/v1/app/teams/{team}/config` | team:manage |
| GET | `/api/v1/app/teams/{team}/permissions/pre-requisite` | team:manage |
| POST | `/api/v1/app/teams/{team}/permissions/role/assign` | team:manage |
| GET | `/api/v1/app/teams/{team}/permissions/search` | team:manage |
| GET | `/api/v1/app/teams/{team}/permissions/user/search` | team:manage |
| POST | `/api/v1/app/teams/{team}/permissions/user/assign` | team:manage |
| GET | `/api/v1/app/users/pre-requisite` |  |
| POST | `/api/v1/app/users/scope` |  |
| POST | `/api/v1/app/users/{user}/status` |  |
| POST | `/api/v1/app/users/{user}/toggle-force-change-password` |  |
| POST | `/api/v1/app/users/{user}/impersonate` |  |
| POST | `/api/v1/app/users/unimpersonate` |  |
| GET | `/api/v1/app/users` |  |
| POST | `/api/v1/app/users` |  |
| GET | `/api/v1/app/users/{id}` |  |
| PUT|PATCH | `/api/v1/app/users/{id}` |  |
| DELETE | `/api/v1/app/users/{id}` |  |
| POST | `/api/v1/app/user/preference` |  |
| POST | `/api/v1/app/user/force-change-password` |  |
| GET | `/api/v1/app/setup-wizard` |  |
| GET | `/api/v1/app/failed-login-attempts` |  |
| GET | `/api/v1/app/bulk-upload/action/pre-requisite` |  |
| POST | `/api/v1/app/bulk-upload/action` |  |
| POST | `/api/v1/app/attendance/qr-code` |  |
| POST | `/api/v1/app/attendance/mark` |  |
| POST | `/api/v1/app/user/password` |  |
| POST | `/api/v1/app/user/profile` |  |
| POST | `/api/v1/app/user/profile/account` |  |
| POST | `/api/v1/app/user/profile/verify` |  |
| POST | `/api/v1/app/user/profile/avatar` |  |
| DELETE | `/api/v1/app/user/profile/avatar` |  |
| GET | `/api/v1/app/notifications` |  |
| POST | `/api/v1/app/notifications/{notification}/mark-as-read` |  |
| POST | `/api/v1/app/notifications/mark-all-as-read` |  |
| GET | `/api/v1/app/reminders` |  |
| POST | `/api/v1/app/reminders` |  |
| GET | `/api/v1/app/reminders/{id}` |  |
| PUT|PATCH | `/api/v1/app/reminders/{id}` |  |
| DELETE | `/api/v1/app/reminders/{id}` |  |
| GET | `/api/v1/app/dashboard/stat` | dashboard:stat |
| GET | `/api/v1/app/dashboard/student-chart-data` | dashboard:stat |
| GET | `/api/v1/app/dashboard/transaction-chart-data` | dashboard:stat |
| GET | `/api/v1/app/dashboard/employee-attendance-summary` | dashboard:stat |
| GET | `/api/v1/app/dashboard/schedule` |  |
| GET | `/api/v1/app/dashboard/timetable` |  |
| GET | `/api/v1/app/dashboard/student` |  |
| GET | `/api/v1/app/dashboard/transport-route` |  |
| GET | `/api/v1/app/dashboard/mess-schedule` |  |
| GET | `/api/v1/app/dashboard/institute-info` |  |
| GET | `/api/v1/app/dashboard/form-list` |  |
| GET | `/api/v1/app/dashboard/gallery` |  |
| GET | `/api/v1/app/dashboard/celebration` |  |
| GET | `/api/v1/app/search` |  |
| GET | `/api/v1/app/config/module-pre-requisite` |  |
| GET | `/api/v1/app/config/, [ConfigController::class, ` |  |
| POST | `/api/v1/app/config/, [ConfigController::class, ` |  |
| POST | `/api/v1/app/config/module` |  |
| GET | `/api/v1/app/config/mail/test` |  |
| GET | `/api/v1/app/config/sms/test` |  |
| GET | `/api/v1/app/config/whatsapp/test` |  |
| GET | `/api/v1/app/config/pusher/test` |  |
| GET | `/api/v1/app/config/app/test` |  |
| POST | `/api/v1/app/config/assets` |  |
| DELETE | `/api/v1/app/config/assets` |  |
| POST | `/api/v1/app/config/templates/{template}/status` | config:store |
| GET | `/api/v1/app/config/mail-templates` | config:store |
| GET | `/api/v1/app/config/mail-templates/{id}` | config:store |
| PUT|PATCH | `/api/v1/app/config/mail-templates/{id}` | config:store |
| GET | `/api/v1/app/config/sms-templates` | config:store |
| GET | `/api/v1/app/config/sms-templates/{id}` | config:store |
| PUT|PATCH | `/api/v1/app/config/sms-templates/{id}` | config:store |
| GET | `/api/v1/app/config/whatsapp-templates` | config:store |
| GET | `/api/v1/app/config/whatsapp-templates/{id}` | config:store |
| PUT|PATCH | `/api/v1/app/config/whatsapp-templates/{id}` | config:store |
| GET | `/api/v1/app/config/push-notification-templates` | config:store |
| GET | `/api/v1/app/config/push-notification-templates/{id}` | config:store |
| PUT|PATCH | `/api/v1/app/config/push-notification-templates/{id}` | config:store |
| POST | `/api/v1/app/config/locales/{locale}/sync` | config:store |
| GET | `/api/v1/app/config/locales` | config:store |
| POST | `/api/v1/app/config/locales` | config:store |
| GET | `/api/v1/app/config/locales/{id}` | config:store |
| PUT|PATCH | `/api/v1/app/config/locales/{id}` | config:store |
| DELETE | `/api/v1/app/config/locales/{id}` | config:store |
| GET | `/api/v1/app/options/pre-requisite` |  |
| POST | `/api/v1/app/options/import` |  |
| POST | `/api/v1/app/options/reorder` |  |
| GET | `/api/v1/app/options` |  |
| POST | `/api/v1/app/options` |  |
| GET | `/api/v1/app/options/{id}` |  |
| PUT|PATCH | `/api/v1/app/options/{id}` |  |
| DELETE | `/api/v1/app/options/{id}` |  |
| GET | `/api/v1/app/custom-fields/pre-requisite` |  |
| GET | `/api/v1/app/custom-fields` |  |
| POST | `/api/v1/app/custom-fields` |  |
| GET | `/api/v1/app/custom-fields/{id}` |  |
| PUT|PATCH | `/api/v1/app/custom-fields/{id}` |  |
| DELETE | `/api/v1/app/custom-fields/{id}` |  |
| POST | `/api/v1/app/comments` |  |
| GET | `/api/v1/app/utility/todos/pre-requisite` | todo:manage |
| POST | `/api/v1/app/utility/todos/{todo}/status` | todo:manage |
| POST | `/api/v1/app/utility/todos/{todo}/archive` | todo:manage |
| POST | `/api/v1/app/utility/todos/{todo}/unarchive` | todo:manage |
| POST | `/api/v1/app/utility/todos/reorder` | todo:manage |
| POST | `/api/v1/app/utility/todos/lists/move` | todo:manage |
| POST | `/api/v1/app/utility/todos/delete` |  |
| GET | `/api/v1/app/utility/todos` | todo:manage |
| POST | `/api/v1/app/utility/todos` | todo:manage |
| GET | `/api/v1/app/utility/todos/{id}` | todo:manage |
| PUT|PATCH | `/api/v1/app/utility/todos/{id}` | todo:manage |
| DELETE | `/api/v1/app/utility/todos/{id}` | todo:manage |
| POST | `/api/v1/app/utility/backups` | backup:manage |
| GET | `/api/v1/app/utility/backups` | backup:manage |
| DELETE | `/api/v1/app/utility/backups/{id}` | backup:manage |
| GET | `/api/v1/app/utility/activity-logs` | activity-log:manage |
| DELETE | `/api/v1/app/utility/activity-logs/{id}` | activity-log:manage |
| POST | `/api/v1/app/images/upload` |  |
| GET | `/api/v1/app/tags` |  |
| POST | `/api/v1/app/medias` |  |
| DELETE | `/api/v1/app/medias/{id}` |  |

## exam  (116 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/exam/grades/pre-requisite` | exam-grade:manage |
| GET | `/api/v1/app/exam/grades` | exam-grade:manage |
| POST | `/api/v1/app/exam/grades` | exam-grade:manage |
| GET | `/api/v1/app/exam/grades/{id}` | exam-grade:manage |
| PUT|PATCH | `/api/v1/app/exam/grades/{id}` | exam-grade:manage |
| DELETE | `/api/v1/app/exam/grades/{id}` | exam-grade:manage |
| GET | `/api/v1/app/exam/assessments/pre-requisite` | exam-assessment:manage |
| GET | `/api/v1/app/exam/assessments` | exam-assessment:manage |
| POST | `/api/v1/app/exam/assessments` | exam-assessment:manage |
| GET | `/api/v1/app/exam/assessments/{id}` | exam-assessment:manage |
| PUT|PATCH | `/api/v1/app/exam/assessments/{id}` | exam-assessment:manage |
| DELETE | `/api/v1/app/exam/assessments/{id}` | exam-assessment:manage |
| GET | `/api/v1/app/exam/observations/pre-requisite` | exam-observation:manage |
| GET | `/api/v1/app/exam/observations` | exam-observation:manage |
| POST | `/api/v1/app/exam/observations` | exam-observation:manage |
| GET | `/api/v1/app/exam/observations/{id}` | exam-observation:manage |
| PUT|PATCH | `/api/v1/app/exam/observations/{id}` | exam-observation:manage |
| DELETE | `/api/v1/app/exam/observations/{id}` | exam-observation:manage |
| GET | `/api/v1/app/exam/competencies/pre-requisite` | exam-competency:manage |
| GET | `/api/v1/app/exam/competencies` | exam-competency:manage |
| POST | `/api/v1/app/exam/competencies` | exam-competency:manage |
| GET | `/api/v1/app/exam/competencies/{id}` | exam-competency:manage |
| PUT|PATCH | `/api/v1/app/exam/competencies/{id}` | exam-competency:manage |
| DELETE | `/api/v1/app/exam/competencies/{id}` | exam-competency:manage |
| GET | `/api/v1/app/exam/terms/pre-requisite` | exam-term:manage |
| POST | `/api/v1/app/exam/terms/reorder` |  |
| GET | `/api/v1/app/exam/terms` | exam-term:manage |
| POST | `/api/v1/app/exam/terms` | exam-term:manage |
| GET | `/api/v1/app/exam/terms/{id}` | exam-term:manage |
| PUT|PATCH | `/api/v1/app/exam/terms/{id}` | exam-term:manage |
| DELETE | `/api/v1/app/exam/terms/{id}` | exam-term:manage |
| GET | `/api/v1/app/exam/schedules/pre-requisite` |  |
| PATCH | `/api/v1/app/exam/schedules/{schedule}/toggle-publish-admit-card` |  |
| PATCH | `/api/v1/app/exam/schedules/{schedule}/form` |  |
| POST | `/api/v1/app/exam/schedules/{schedule}/form/confirm` |  |
| POST | `/api/v1/app/exam/schedules/{schedule}/form` |  |
| POST | `/api/v1/app/exam/schedules/{schedule}/copy` |  |
| POST | `/api/v1/app/exam/schedules/{schedule}/config` |  |
| POST | `/api/v1/app/exam/schedules/{schedule}/unlock-temporarily/{uuid}` |  |
| POST | `/api/v1/app/exam/schedules/{schedule}/unlock-temporarily` |  |
| GET | `/api/v1/app/exam/schedules` |  |
| POST | `/api/v1/app/exam/schedules` |  |
| GET | `/api/v1/app/exam/schedules/{id}` |  |
| PUT|PATCH | `/api/v1/app/exam/schedules/{id}` |  |
| DELETE | `/api/v1/app/exam/schedules/{id}` |  |
| GET | `/api/v1/app/exam/online-exams/pre-requisite` |  |
| GET | `/api/v1/app/exam/online-exams/{onlineExam}/questions/pre-requisite` |  |
| POST | `/api/v1/app/exam/online-exams/{onlineExam}/questions/reorder` |  |
| GET | `/api/v1/app/exam/online-exams.questions` |  |
| POST | `/api/v1/app/exam/online-exams.questions` |  |
| GET | `/api/v1/app/exam/online-exams.questions/{id}` |  |
| PUT|PATCH | `/api/v1/app/exam/online-exams.questions/{id}` |  |
| DELETE | `/api/v1/app/exam/online-exams.questions/{id}` |  |
| GET | `/api/v1/app/exam/online-exams/{onlineExam}/submissions/{submission}/questions` |  |
| POST | `/api/v1/app/exam/online-exams/{onlineExam}/submissions/{submission}/evaluate` |  |
| GET | `/api/v1/app/exam/online-exams.submissions` |  |
| DELETE | `/api/v1/app/exam/online-exams.submissions/{id}` |  |
| GET | `/api/v1/app/exam/online-exams/{onlineExam}/live-questions` |  |
| POST | `/api/v1/app/exam/online-exams/{onlineExam}/start` |  |
| POST | `/api/v1/app/exam/online-exams/{onlineExam}/submit` |  |
| POST | `/api/v1/app/exam/online-exams/{onlineExam}/finish-submit` |  |
| POST | `/api/v1/app/exam/online-exams/{onlineExam}/status` |  |
| GET | `/api/v1/app/exam/online-exams` |  |
| POST | `/api/v1/app/exam/online-exams` |  |
| GET | `/api/v1/app/exam/online-exams/{id}` |  |
| PUT|PATCH | `/api/v1/app/exam/online-exams/{id}` |  |
| DELETE | `/api/v1/app/exam/online-exams/{id}` |  |
| GET | `/api/v1/app/exam/forms/pre-requisite` |  |
| POST | `/api/v1/app/exam/forms/{form}/status` |  |
| GET | `/api/v1/app/exam/forms/{form}/print` |  |
| GET | `/api/v1/app/exam/forms/{form}/print-admit-card` |  |
| GET | `/api/v1/app/exam/forms` |  |
| GET | `/api/v1/app/exam/forms/{id}` |  |
| DELETE | `/api/v1/app/exam/forms/{id}` |  |
| GET | `/api/v1/app/exam/mark/pre-requisite` |  |
| GET | `/api/v1/app/exam/mark/fetch` |  |
| POST | `/api/v1/app/exam/mark` |  |
| DELETE | `/api/v1/app/exam/mark` |  |
| GET | `/api/v1/app/exam/observation-mark/pre-requisite` |  |
| GET | `/api/v1/app/exam/observation-mark/fetch` |  |
| POST | `/api/v1/app/exam/observation-mark` |  |
| DELETE | `/api/v1/app/exam/observation-mark` |  |
| GET | `/api/v1/app/exam/competency-evaluation/pre-requisite` |  |
| GET | `/api/v1/app/exam/competency-evaluation/fetch` |  |
| POST | `/api/v1/app/exam/competency-evaluation` |  |
| DELETE | `/api/v1/app/exam/competency-evaluation` |  |
| GET | `/api/v1/app/exam/comment/pre-requisite` |  |
| GET | `/api/v1/app/exam/comment/fetch` |  |
| POST | `/api/v1/app/exam/comment` |  |
| DELETE | `/api/v1/app/exam/comment` |  |
| GET | `/api/v1/app/exam/attendance/pre-requisite` |  |
| GET | `/api/v1/app/exam/attendance/fetch` |  |
| POST | `/api/v1/app/exam/attendance` |  |
| DELETE | `/api/v1/app/exam/attendance` |  |
| GET | `/api/v1/app/exam/admit-card/pre-requisite` | exam-admit-card:access |
| GET | `/api/v1/app/exam/admit-card` | exam-admit-card:access |
| GET | `/api/v1/app/exam/marksheet/pre-requisite` | exam-marksheet:access |
| GET | `/api/v1/app/exam/marksheet` | exam-marksheet:access |
| GET | `/api/v1/app/exam/marksheet/process/pre-requisite` | exam-marksheet:access |
| GET | `/api/v1/app/exam/marksheet/process` | exam-marksheet:access |
| GET | `/api/v1/app/exam/marksheet/print/pre-requisite` | exam-marksheet:access |
| GET | `/api/v1/app/exam/marksheet/print` | exam-marksheet:access |
| GET | `/api/v1/app/exam/reports/mark-summary/pre-requisite` | exam:report |
| GET | `/api/v1/app/exam/reports/mark-summary` | exam:report |
| GET | `/api/v1/app/exam/reports/exam-summary/pre-requisite` | exam:report |
| GET | `/api/v1/app/exam/reports/exam-summary` | exam:report |
| GET | `/api/v1/app/exams/pre-requisite` | exam:manage |
| POST | `/api/v1/app/exams/{exam}/config` | exam:manage |
| POST | `/api/v1/app/exams/reorder` | exam:manage |
| POST | `/api/v1/app/exams/{exam}/signatures/{type}` | exam:manage |
| DELETE | `/api/v1/app/exams/{exam}/signatures/{type}` | exam:manage |
| GET | `/api/v1/app/exams` | exam:manage |
| POST | `/api/v1/app/exams` | exam:manage |
| GET | `/api/v1/app/exams/{id}` | exam:manage |
| PUT|PATCH | `/api/v1/app/exams/{id}` | exam:manage |
| DELETE | `/api/v1/app/exams/{id}` | exam:manage |

## finance  (115 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/finance/payment-methods/pre-requisite` | finance:config |
| GET | `/api/v1/app/finance/payment-methods` | finance:config |
| POST | `/api/v1/app/finance/payment-methods` | finance:config |
| GET | `/api/v1/app/finance/payment-methods/{id}` | finance:config |
| PUT|PATCH | `/api/v1/app/finance/payment-methods/{id}` | finance:config |
| DELETE | `/api/v1/app/finance/payment-methods/{id}` | finance:config |
| GET | `/api/v1/app/finance/ledger-types/pre-requisite` |  |
| GET | `/api/v1/app/finance/ledger-types` |  |
| POST | `/api/v1/app/finance/ledger-types` |  |
| GET | `/api/v1/app/finance/ledger-types/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/ledger-types/{id}` |  |
| DELETE | `/api/v1/app/finance/ledger-types/{id}` |  |
| GET | `/api/v1/app/finance/taxes/pre-requisite` |  |
| GET | `/api/v1/app/finance/taxes` |  |
| POST | `/api/v1/app/finance/taxes` |  |
| GET | `/api/v1/app/finance/taxes/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/taxes/{id}` |  |
| DELETE | `/api/v1/app/finance/taxes/{id}` |  |
| GET | `/api/v1/app/finance/ledgers/pre-requisite` |  |
| GET | `/api/v1/app/finance/ledgers` |  |
| POST | `/api/v1/app/finance/ledgers` |  |
| GET | `/api/v1/app/finance/ledgers/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/ledgers/{id}` |  |
| DELETE | `/api/v1/app/finance/ledgers/{id}` |  |
| GET | `/api/v1/app/finance/transactions/pre-requisite` |  |
| POST | `/api/v1/app/finance/transactions/{transaction}/clearing-date` |  |
| POST | `/api/v1/app/finance/transactions/import` | transaction:create |
| GET | `/api/v1/app/finance/transactions` |  |
| POST | `/api/v1/app/finance/transactions` |  |
| GET | `/api/v1/app/finance/transactions/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/transactions/{id}` |  |
| DELETE | `/api/v1/app/finance/transactions/{id}` |  |
| GET | `/api/v1/app/finance/receipts/pre-requisite` |  |
| GET | `/api/v1/app/finance/receipts` |  |
| POST | `/api/v1/app/finance/receipts` |  |
| GET | `/api/v1/app/finance/receipts/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/receipts/{id}` |  |
| DELETE | `/api/v1/app/finance/receipts/{id}` |  |
| POST | `/api/v1/app/finance/day-closure` | transaction:read |
| GET | `/api/v1/app/finance/day-closures/pre-requisite` |  |
| POST | `/api/v1/app/finance/day-closures/date-wise-collection` |  |
| GET | `/api/v1/app/finance/day-closures` |  |
| POST | `/api/v1/app/finance/day-closures` |  |
| GET | `/api/v1/app/finance/day-closures/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/day-closures/{id}` |  |
| DELETE | `/api/v1/app/finance/day-closures/{id}` |  |
| GET | `/api/v1/app/finance/fee-groups/pre-requisite` |  |
| GET | `/api/v1/app/finance/fee-groups` |  |
| POST | `/api/v1/app/finance/fee-groups` |  |
| GET | `/api/v1/app/finance/fee-groups/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/fee-groups/{id}` |  |
| DELETE | `/api/v1/app/finance/fee-groups/{id}` |  |
| GET | `/api/v1/app/finance/fee-components/pre-requisite` |  |
| GET | `/api/v1/app/finance/fee-components` |  |
| POST | `/api/v1/app/finance/fee-components` |  |
| GET | `/api/v1/app/finance/fee-components/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/fee-components/{id}` |  |
| DELETE | `/api/v1/app/finance/fee-components/{id}` |  |
| GET | `/api/v1/app/finance/fee-heads/pre-requisite` |  |
| GET | `/api/v1/app/finance/fee-heads` |  |
| POST | `/api/v1/app/finance/fee-heads` |  |
| GET | `/api/v1/app/finance/fee-heads/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/fee-heads/{id}` |  |
| DELETE | `/api/v1/app/finance/fee-heads/{id}` |  |
| GET | `/api/v1/app/finance/fee-concessions/pre-requisite` |  |
| GET | `/api/v1/app/finance/fee-concessions` |  |
| POST | `/api/v1/app/finance/fee-concessions` |  |
| GET | `/api/v1/app/finance/fee-concessions/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/fee-concessions/{id}` |  |
| DELETE | `/api/v1/app/finance/fee-concessions/{id}` |  |
| GET | `/api/v1/app/finance/fee-structures/pre-requisite` |  |
| POST | `/api/v1/app/finance/fee-structures/{fee_structure}/allocation` |  |
| DELETE | `/api/v1/app/finance/fee-structures/{fee_structure}/allocations/{allocation}` |  |
| POST | `/api/v1/app/finance/fee-structures/{fee_structure}/installments` |  |
| GET | `/api/v1/app/finance/fee-structures/{fee_structure}/installments/{uuid}` |  |
| PATCH | `/api/v1/app/finance/fee-structures/{fee_structure}/installments/{uuid}` |  |
| DELETE | `/api/v1/app/finance/fee-structures/{fee_structure}/installments/{uuid}` |  |
| GET | `/api/v1/app/finance/fee-structures/{fee_structure}/optional-fee-heads` |  |
| GET | `/api/v1/app/finance/fee-structures` |  |
| POST | `/api/v1/app/finance/fee-structures` |  |
| GET | `/api/v1/app/finance/fee-structures/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/fee-structures/{id}` |  |
| DELETE | `/api/v1/app/finance/fee-structures/{id}` |  |
| GET | `/api/v1/app/finance/fee-structure-components/pre-requisite` |  |
| GET | `/api/v1/app/finance/fee-structure-components` |  |
| POST | `/api/v1/app/finance/fee-structure-components` |  |
| GET | `/api/v1/app/finance/fee-structure-components/{id}` |  |
| PUT|PATCH | `/api/v1/app/finance/fee-structure-components/{id}` |  |
| DELETE | `/api/v1/app/finance/fee-structure-components/{id}` |  |
| GET | `/api/v1/app/finance/reports/day-book/pre-requisite` | transaction:read |
| GET | `/api/v1/app/finance/reports/day-book` | transaction:read |
| GET | `/api/v1/app/finance/reports/fee-payment/pre-requisite` | transaction:read |
| GET | `/api/v1/app/finance/reports/fee-payment` | transaction:read |
| GET | `/api/v1/app/finance/reports/online-fee-payment/pre-requisite` | transaction:read |
| GET | `/api/v1/app/finance/reports/online-fee-payment` | transaction:read |
| GET | `/api/v1/app/finance/reports/bank-transfer/pre-requisite` | transaction:read |
| GET | `/api/v1/app/finance/reports/bank-transfer` | transaction:read |
| GET | `/api/v1/app/finance/reports/fee-refund/pre-requisite` | transaction:read |
| GET | `/api/v1/app/finance/reports/fee-refund` | transaction:read |
| GET | `/api/v1/app/finance/reports/fee-summary/pre-requisite` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-summary` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-concession/pre-requisite` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-concession` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-concession-summary/pre-requisite` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-concession-summary` | finance:report |
| GET | `/api/v1/app/finance/reports/installment-wise-fee-due/pre-requisite` | finance:report |
| GET | `/api/v1/app/finance/reports/installment-wise-fee-due` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-due/pre-requisite` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-due` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-head/pre-requisite` | finance:report |
| GET | `/api/v1/app/finance/reports/fee-head` | finance:report |
| GET | `/api/v1/app/finance/reports/head-wise-fee-payment/pre-requisite` | finance:report |
| GET | `/api/v1/app/finance/reports/head-wise-fee-payment` | finance:report |
| GET | `/api/v1/app/finance/reports/payment-method-wise-fee-payment/pre-requisite` | finance:report |
| GET | `/api/v1/app/finance/reports/payment-method-wise-fee-payment` | finance:report |

## transport  (95 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/transport/vehicle/config/document-types` | transport:config |
| POST | `/api/v1/app/transport/vehicle/config/document-types` | transport:config |
| GET | `/api/v1/app/transport/vehicle/config/document-types/{id}` | transport:config |
| PUT|PATCH | `/api/v1/app/transport/vehicle/config/document-types/{id}` | transport:config |
| DELETE | `/api/v1/app/transport/vehicle/config/document-types/{id}` | transport:config |
| GET | `/api/v1/app/transport/vehicle/config/expense-types` | transport:config |
| POST | `/api/v1/app/transport/vehicle/config/expense-types` | transport:config |
| GET | `/api/v1/app/transport/vehicle/config/expense-types/{id}` | transport:config |
| PUT|PATCH | `/api/v1/app/transport/vehicle/config/expense-types/{id}` | transport:config |
| DELETE | `/api/v1/app/transport/vehicle/config/expense-types/{id}` | transport:config |
| GET | `/api/v1/app/transport/stoppages/pre-requisite` | transport-stoppage:manage |
| POST | `/api/v1/app/transport/stoppages/import` | transport-stoppage:manage |
| GET | `/api/v1/app/transport/stoppages` | transport-stoppage:manage |
| POST | `/api/v1/app/transport/stoppages` | transport-stoppage:manage |
| GET | `/api/v1/app/transport/stoppages/{id}` | transport-stoppage:manage |
| PUT|PATCH | `/api/v1/app/transport/stoppages/{id}` | transport-stoppage:manage |
| DELETE | `/api/v1/app/transport/stoppages/{id}` | transport-stoppage:manage |
| GET | `/api/v1/app/transport/routes/pre-requisite` |  |
| DELETE | `/api/v1/app/transport/routes/{route}/passengers/{passenger}` |  |
| POST | `/api/v1/app/transport/routes/{route}/students` |  |
| POST | `/api/v1/app/transport/routes/{route}/employees` |  |
| GET | `/api/v1/app/transport/routes` |  |
| POST | `/api/v1/app/transport/routes` |  |
| GET | `/api/v1/app/transport/routes/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/routes/{id}` |  |
| DELETE | `/api/v1/app/transport/routes/{id}` |  |
| GET | `/api/v1/app/transport/circles/pre-requisite` |  |
| GET | `/api/v1/app/transport/circles` |  |
| POST | `/api/v1/app/transport/circles` |  |
| GET | `/api/v1/app/transport/circles/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/circles/{id}` |  |
| DELETE | `/api/v1/app/transport/circles/{id}` |  |
| GET | `/api/v1/app/transport/fees/pre-requisite` |  |
| GET | `/api/v1/app/transport/fees` |  |
| POST | `/api/v1/app/transport/fees` |  |
| GET | `/api/v1/app/transport/fees/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/fees/{id}` |  |
| DELETE | `/api/v1/app/transport/fees/{id}` |  |
| GET | `/api/v1/app/transport/vehicles/pre-requisite` |  |
| POST | `/api/v1/app/transport/vehicles/import` | vehicle:create |
| GET | `/api/v1/app/transport/vehicles` |  |
| POST | `/api/v1/app/transport/vehicles` |  |
| GET | `/api/v1/app/transport/vehicles/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/vehicles/{id}` |  |
| DELETE | `/api/v1/app/transport/vehicles/{id}` |  |
| GET | `/api/v1/app/transport/reports/batch-wise-route/pre-requisite` | transport:report |
| GET | `/api/v1/app/transport/reports/batch-wise-route` | transport:report |
| GET | `/api/v1/app/transport/reports/route-wise-student/pre-requisite` | transport:report |
| GET | `/api/v1/app/transport/reports/route-wise-student` | transport:report |
| GET | `/api/v1/app/transport/vehicle/incharges/pre-requisite` |  |
| POST | `/api/v1/app/transport/vehicle/incharges/import` | vehicle-incharge:create |
| GET | `/api/v1/app/transport/vehicle/incharges` |  |
| POST | `/api/v1/app/transport/vehicle/incharges` |  |
| GET | `/api/v1/app/transport/vehicle/incharges/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/vehicle/incharges/{id}` |  |
| DELETE | `/api/v1/app/transport/vehicle/incharges/{id}` |  |
| GET | `/api/v1/app/transport/vehicle/documents/pre-requisite` |  |
| POST | `/api/v1/app/transport/vehicle/documents/import` | vehicle-document:create |
| GET | `/api/v1/app/transport/vehicle/documents` |  |
| POST | `/api/v1/app/transport/vehicle/documents` |  |
| GET | `/api/v1/app/transport/vehicle/documents/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/vehicle/documents/{id}` |  |
| DELETE | `/api/v1/app/transport/vehicle/documents/{id}` |  |
| GET | `/api/v1/app/transport/vehicle/fuel-records/pre-requisite` |  |
| POST | `/api/v1/app/transport/vehicle/fuel-records/previous-log` |  |
| GET | `/api/v1/app/transport/vehicle/fuel-records` |  |
| POST | `/api/v1/app/transport/vehicle/fuel-records` |  |
| GET | `/api/v1/app/transport/vehicle/fuel-records/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/vehicle/fuel-records/{id}` |  |
| DELETE | `/api/v1/app/transport/vehicle/fuel-records/{id}` |  |
| GET | `/api/v1/app/transport/vehicle/trip-records/pre-requisite` |  |
| GET | `/api/v1/app/transport/vehicle/trip-records` |  |
| POST | `/api/v1/app/transport/vehicle/trip-records` |  |
| GET | `/api/v1/app/transport/vehicle/trip-records/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/vehicle/trip-records/{id}` |  |
| DELETE | `/api/v1/app/transport/vehicle/trip-records/{id}` |  |
| GET | `/api/v1/app/transport/vehicle/service-records/pre-requisite` |  |
| GET | `/api/v1/app/transport/vehicle/service-records` |  |
| POST | `/api/v1/app/transport/vehicle/service-records` |  |
| GET | `/api/v1/app/transport/vehicle/service-records/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/vehicle/service-records/{id}` |  |
| DELETE | `/api/v1/app/transport/vehicle/service-records/{id}` |  |
| GET | `/api/v1/app/transport/vehicle/case-records/pre-requisite` |  |
| GET | `/api/v1/app/transport/vehicle/case-records` |  |
| POST | `/api/v1/app/transport/vehicle/case-records` |  |
| GET | `/api/v1/app/transport/vehicle/case-records/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/vehicle/case-records/{id}` |  |
| DELETE | `/api/v1/app/transport/vehicle/case-records/{id}` |  |
| GET | `/api/v1/app/transport/vehicle/expense-records/pre-requisite` |  |
| POST | `/api/v1/app/transport/vehicle/expense-records/import` | vehicle-expense-record:create |
| GET | `/api/v1/app/transport/vehicle/expense-records` |  |
| POST | `/api/v1/app/transport/vehicle/expense-records` |  |
| GET | `/api/v1/app/transport/vehicle/expense-records/{id}` |  |
| PUT|PATCH | `/api/v1/app/transport/vehicle/expense-records/{id}` |  |
| DELETE | `/api/v1/app/transport/vehicle/expense-records/{id}` |  |

## inventory  (76 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/inventories/pre-requisite` | inventory:config |
| GET | `/api/v1/app/inventories` | inventory:config |
| POST | `/api/v1/app/inventories` | inventory:config |
| GET | `/api/v1/app/inventories/{id}` | inventory:config |
| PUT|PATCH | `/api/v1/app/inventories/{id}` | inventory:config |
| DELETE | `/api/v1/app/inventories/{id}` | inventory:config |
| GET | `/api/v1/app/inventory/incharges/pre-requisite` | inventory:config |
| GET | `/api/v1/app/inventory/incharges` | inventory:config |
| POST | `/api/v1/app/inventory/incharges` | inventory:config |
| GET | `/api/v1/app/inventory/incharges/{id}` | inventory:config |
| PUT|PATCH | `/api/v1/app/inventory/incharges/{id}` | inventory:config |
| DELETE | `/api/v1/app/inventory/incharges/{id}` | inventory:config |
| GET | `/api/v1/app/inventory/vendors/pre-requisite` |  |
| GET | `/api/v1/app/inventory/vendors/{vendor}/statement` |  |
| POST | `/api/v1/app/inventory/vendors/import` | vendor:create |
| GET | `/api/v1/app/inventory/vendors` |  |
| POST | `/api/v1/app/inventory/vendors` |  |
| GET | `/api/v1/app/inventory/vendors/{id}` |  |
| PUT|PATCH | `/api/v1/app/inventory/vendors/{id}` |  |
| DELETE | `/api/v1/app/inventory/vendors/{id}` |  |
| GET | `/api/v1/app/inventory/stock-categories/pre-requisite` |  |
| POST | `/api/v1/app/inventory/stock-categories/import` | stock-category:create |
| GET | `/api/v1/app/inventory/stock-categories` |  |
| POST | `/api/v1/app/inventory/stock-categories` |  |
| GET | `/api/v1/app/inventory/stock-categories/{id}` |  |
| PUT|PATCH | `/api/v1/app/inventory/stock-categories/{id}` |  |
| DELETE | `/api/v1/app/inventory/stock-categories/{id}` |  |
| GET | `/api/v1/app/inventory/stock-items/pre-requisite` |  |
| POST | `/api/v1/app/inventory/stock-items/import` | stock-item:create |
| POST | `/api/v1/app/inventory/stock-items/{stockItem}/quantity` |  |
| POST | `/api/v1/app/inventory/stock-items/tags` | stock-item:edit |
| GET | `/api/v1/app/inventory/stock-items` |  |
| POST | `/api/v1/app/inventory/stock-items` |  |
| GET | `/api/v1/app/inventory/stock-items/{id}` |  |
| PUT|PATCH | `/api/v1/app/inventory/stock-items/{id}` |  |
| DELETE | `/api/v1/app/inventory/stock-items/{id}` |  |
| GET | `/api/v1/app/inventory/stock-items-with-copies` |  |
| GET | `/api/v1/app/inventory/stock-item/copies/pre-requisite` |  |
| POST | `/api/v1/app/inventory/stock-item/copies/condition` | stock-item:edit |
| POST | `/api/v1/app/inventory/stock-item/copies/status` | stock-item:edit |
| GET | `/api/v1/app/inventory/stock-item/copies` |  |
| POST | `/api/v1/app/inventory/stock-item/copies/tags` | stock-item:edit |
| GET | `/api/v1/app/inventory/stock-item/labels/pre-requisite` |  |
| GET | `/api/v1/app/inventory/stock-item/labels` |  |
| GET | `/api/v1/app/inventory/stock-requisitions/pre-requisite` |  |
| GET | `/api/v1/app/inventory/stock-requisitions` |  |
| POST | `/api/v1/app/inventory/stock-requisitions` |  |
| GET | `/api/v1/app/inventory/stock-requisitions/{id}` |  |
| PUT|PATCH | `/api/v1/app/inventory/stock-requisitions/{id}` |  |
| DELETE | `/api/v1/app/inventory/stock-requisitions/{id}` |  |
| GET | `/api/v1/app/inventory/stock-purchases/pre-requisite` |  |
| GET | `/api/v1/app/inventory/stock-purchases` |  |
| POST | `/api/v1/app/inventory/stock-purchases` |  |
| GET | `/api/v1/app/inventory/stock-purchases/{id}` |  |
| PUT|PATCH | `/api/v1/app/inventory/stock-purchases/{id}` |  |
| DELETE | `/api/v1/app/inventory/stock-purchases/{id}` |  |
| GET | `/api/v1/app/inventory/stock-returns/pre-requisite` |  |
| GET | `/api/v1/app/inventory/stock-returns` |  |
| POST | `/api/v1/app/inventory/stock-returns` |  |
| GET | `/api/v1/app/inventory/stock-returns/{id}` |  |
| PUT|PATCH | `/api/v1/app/inventory/stock-returns/{id}` |  |
| DELETE | `/api/v1/app/inventory/stock-returns/{id}` |  |
| GET | `/api/v1/app/inventory/stock-transfers/pre-requisite` |  |
| GET | `/api/v1/app/inventory/stock-transfers` |  |
| POST | `/api/v1/app/inventory/stock-transfers` |  |
| GET | `/api/v1/app/inventory/stock-transfers/{id}` |  |
| PUT|PATCH | `/api/v1/app/inventory/stock-transfers/{id}` |  |
| DELETE | `/api/v1/app/inventory/stock-transfers/{id}` |  |
| GET | `/api/v1/app/inventory/stock-adjustments/pre-requisite` |  |
| GET | `/api/v1/app/inventory/stock-adjustments` |  |
| POST | `/api/v1/app/inventory/stock-adjustments` |  |
| GET | `/api/v1/app/inventory/stock-adjustments/{id}` |  |
| PUT|PATCH | `/api/v1/app/inventory/stock-adjustments/{id}` |  |
| DELETE | `/api/v1/app/inventory/stock-adjustments/{id}` |  |
| GET | `/api/v1/app/inventory/reports/item-summary/pre-requisite` | inventory:report |
| GET | `/api/v1/app/inventory/reports/item-summary` | inventory:report |

## reception  (73 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/reception/enquiries/pre-requisite` |  |
| POST | `/api/v1/app/reception/enquiries/import` | enquiry:create |
| GET | `/api/v1/app/reception/enquiries/{enquiry}/documents/pre-requisite` |  |
| POST | `/api/v1/app/reception/enquiries.documents` |  |
| GET | `/api/v1/app/reception/enquiries.documents/{id}` |  |
| PUT|PATCH | `/api/v1/app/reception/enquiries.documents/{id}` |  |
| DELETE | `/api/v1/app/reception/enquiries.documents/{id}` |  |
| GET | `/api/v1/app/reception/enquiries/{enquiry}/qualifications/pre-requisite` |  |
| POST | `/api/v1/app/reception/enquiries.qualifications` |  |
| GET | `/api/v1/app/reception/enquiries.qualifications/{id}` |  |
| PUT|PATCH | `/api/v1/app/reception/enquiries.qualifications/{id}` |  |
| DELETE | `/api/v1/app/reception/enquiries.qualifications/{id}` |  |
| GET | `/api/v1/app/reception/enquiries/{enquiry}/follow-ups/pre-requisite` |  |
| POST | `/api/v1/app/reception/enquiries.follow-ups` |  |
| DELETE | `/api/v1/app/reception/enquiries.follow-ups/{id}` |  |
| POST | `/api/v1/app/reception/enquiries/{enquiry}/photo` |  |
| DELETE | `/api/v1/app/reception/enquiries/{enquiry}/photo` |  |
| POST | `/api/v1/app/reception/enquiries/{enquiry}/registration` |  |
| POST | `/api/v1/app/reception/enquiries/registration` |  |
| POST | `/api/v1/app/reception/enquiries/assign` |  |
| POST | `/api/v1/app/reception/enquiries/stage` |  |
| POST | `/api/v1/app/reception/enquiries/type` |  |
| POST | `/api/v1/app/reception/enquiries/source` |  |
| POST | `/api/v1/app/reception/enquiries/delete` |  |
| POST | `/api/v1/app/reception/enquiries/{enquiry}/detail` |  |
| GET | `/api/v1/app/reception/enquiries/{enquiry}/guardians` |  |
| GET | `/api/v1/app/reception/enquiries/{enquiry}/documents` |  |
| GET | `/api/v1/app/reception/enquiries/{enquiry}/qualifications` |  |
| GET | `/api/v1/app/reception/enquiries` |  |
| POST | `/api/v1/app/reception/enquiries` |  |
| GET | `/api/v1/app/reception/enquiries/{id}` |  |
| PUT|PATCH | `/api/v1/app/reception/enquiries/{id}` |  |
| DELETE | `/api/v1/app/reception/enquiries/{id}` |  |
| GET | `/api/v1/app/reception/visitor-logs/pre-requisite` |  |
| POST | `/api/v1/app/reception/visitor-logs/{visitor_log}/exit` |  |
| GET | `/api/v1/app/reception/visitor-logs` |  |
| POST | `/api/v1/app/reception/visitor-logs` |  |
| GET | `/api/v1/app/reception/visitor-logs/{id}` |  |
| PUT|PATCH | `/api/v1/app/reception/visitor-logs/{id}` |  |
| DELETE | `/api/v1/app/reception/visitor-logs/{id}` |  |
| GET | `/api/v1/app/reception/gate-passes/pre-requisite` |  |
| GET | `/api/v1/app/reception/gate-passes` |  |
| POST | `/api/v1/app/reception/gate-passes` |  |
| GET | `/api/v1/app/reception/gate-passes/{id}` |  |
| PUT|PATCH | `/api/v1/app/reception/gate-passes/{id}` |  |
| DELETE | `/api/v1/app/reception/gate-passes/{id}` |  |
| GET | `/api/v1/app/reception/complaints/pre-requisite` |  |
| POST | `/api/v1/app/reception/complaints/{complaint}/assign` |  |
| POST | `/api/v1/app/reception/complaints/{complaint}/unassign/{employee}` |  |
| POST | `/api/v1/app/reception/complaints/{complaint}/logs` |  |
| POST | `/api/v1/app/reception/complaints/{complaint}/logs/{log}` |  |
| GET | `/api/v1/app/reception/complaints` |  |
| POST | `/api/v1/app/reception/complaints` |  |
| GET | `/api/v1/app/reception/complaints/{id}` |  |
| PUT|PATCH | `/api/v1/app/reception/complaints/{id}` |  |
| DELETE | `/api/v1/app/reception/complaints/{id}` |  |
| GET | `/api/v1/app/reception/call-logs/pre-requisite` |  |
| GET | `/api/v1/app/reception/call-logs` |  |
| POST | `/api/v1/app/reception/call-logs` |  |
| GET | `/api/v1/app/reception/call-logs/{id}` |  |
| PUT|PATCH | `/api/v1/app/reception/call-logs/{id}` |  |
| DELETE | `/api/v1/app/reception/call-logs/{id}` |  |
| GET | `/api/v1/app/reception/correspondences/pre-requisite` |  |
| GET | `/api/v1/app/reception/correspondences` |  |
| POST | `/api/v1/app/reception/correspondences` |  |
| GET | `/api/v1/app/reception/correspondences/{id}` |  |
| PUT|PATCH | `/api/v1/app/reception/correspondences/{id}` |  |
| DELETE | `/api/v1/app/reception/correspondences/{id}` |  |
| GET | `/api/v1/app/reception/queries/pre-requisite` |  |
| POST | `/api/v1/app/reception/queries/{query}/action` |  |
| GET | `/api/v1/app/reception/queries` |  |
| GET | `/api/v1/app/reception/queries/{id}` |  |
| DELETE | `/api/v1/app/reception/queries/{id}` |  |

## resource  (53 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/resource/book-lists/pre-requisite` |  |
| GET | `/api/v1/app/resource/online-classes/pre-requisite` |  |
| GET | `/api/v1/app/resource/online-classes` |  |
| POST | `/api/v1/app/resource/online-classes` |  |
| GET | `/api/v1/app/resource/online-classes/{id}` |  |
| PUT|PATCH | `/api/v1/app/resource/online-classes/{id}` |  |
| DELETE | `/api/v1/app/resource/online-classes/{id}` |  |
| GET | `/api/v1/app/resource/assignments/pre-requisite` |  |
| POST | `/api/v1/app/resource/assignments/{assignment}/evaluate` |  |
| GET | `/api/v1/app/resource/assignments.submissions` |  |
| POST | `/api/v1/app/resource/assignments.submissions` |  |
| GET | `/api/v1/app/resource/assignments` |  |
| POST | `/api/v1/app/resource/assignments` |  |
| GET | `/api/v1/app/resource/assignments/{id}` |  |
| PUT|PATCH | `/api/v1/app/resource/assignments/{id}` |  |
| DELETE | `/api/v1/app/resource/assignments/{id}` |  |
| GET | `/api/v1/app/resource/lesson-plans/pre-requisite` |  |
| GET | `/api/v1/app/resource/lesson-plans` |  |
| POST | `/api/v1/app/resource/lesson-plans` |  |
| GET | `/api/v1/app/resource/lesson-plans/{id}` |  |
| PUT|PATCH | `/api/v1/app/resource/lesson-plans/{id}` |  |
| DELETE | `/api/v1/app/resource/lesson-plans/{id}` |  |
| GET | `/api/v1/app/resource/syllabuses/pre-requisite` |  |
| GET | `/api/v1/app/resource/syllabuses` |  |
| POST | `/api/v1/app/resource/syllabuses` |  |
| GET | `/api/v1/app/resource/syllabuses/{id}` |  |
| PUT|PATCH | `/api/v1/app/resource/syllabuses/{id}` |  |
| DELETE | `/api/v1/app/resource/syllabuses/{id}` |  |
| GET | `/api/v1/app/resource/learning-materials/pre-requisite` |  |
| GET | `/api/v1/app/resource/learning-materials` |  |
| POST | `/api/v1/app/resource/learning-materials` |  |
| GET | `/api/v1/app/resource/learning-materials/{id}` |  |
| PUT|PATCH | `/api/v1/app/resource/learning-materials/{id}` |  |
| DELETE | `/api/v1/app/resource/learning-materials/{id}` |  |
| GET | `/api/v1/app/resource/diaries/pre-requisite` |  |
| GET | `/api/v1/app/resource/diaries/preview` |  |
| GET | `/api/v1/app/resource/diaries` |  |
| POST | `/api/v1/app/resource/diaries` |  |
| GET | `/api/v1/app/resource/diaries/{id}` |  |
| PUT|PATCH | `/api/v1/app/resource/diaries/{id}` |  |
| DELETE | `/api/v1/app/resource/diaries/{id}` |  |
| GET | `/api/v1/app/resource/downloads/pre-requisite` |  |
| GET | `/api/v1/app/resource/downloads` |  |
| POST | `/api/v1/app/resource/downloads` |  |
| GET | `/api/v1/app/resource/downloads/{id}` |  |
| PUT|PATCH | `/api/v1/app/resource/downloads/{id}` |  |
| DELETE | `/api/v1/app/resource/downloads/{id}` |  |
| GET | `/api/v1/app/resource/reports/date-wise-student-diary/pre-requisite` | resource:report |
| GET | `/api/v1/app/resource/reports/date-wise-student-diary` | resource:report |
| GET | `/api/v1/app/resource/reports/date-wise-assignment/pre-requisite` | resource:report |
| GET | `/api/v1/app/resource/reports/date-wise-assignment` | resource:report |
| GET | `/api/v1/app/resource/reports/date-wise-learning-material/pre-requisite` | resource:report |
| GET | `/api/v1/app/resource/reports/date-wise-learning-material` | resource:report |

## library  (39 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/library/books/pre-requisite` |  |
| POST | `/api/v1/app/library/books/import` | book:create |
| GET | `/api/v1/app/library/books` |  |
| POST | `/api/v1/app/library/books` |  |
| GET | `/api/v1/app/library/books/{id}` |  |
| PUT|PATCH | `/api/v1/app/library/books/{id}` |  |
| DELETE | `/api/v1/app/library/books/{id}` |  |
| GET | `/api/v1/app/library/book/copies/pre-requisite` |  |
| POST | `/api/v1/app/library/book/copies/condition` | book:edit |
| POST | `/api/v1/app/library/book/copies/status` | book:edit |
| POST | `/api/v1/app/library/book/copies/location` | book:edit |
| POST | `/api/v1/app/library/book/copies/import` | book-addition:create |
| GET | `/api/v1/app/library/book/copies` |  |
| GET | `/api/v1/app/library/book/labels/pre-requisite` |  |
| GET | `/api/v1/app/library/book/labels` |  |
| GET | `/api/v1/app/library/book-additions/pre-requisite` |  |
| GET | `/api/v1/app/library/book-additions` |  |
| POST | `/api/v1/app/library/book-additions` |  |
| GET | `/api/v1/app/library/book-additions/{id}` |  |
| PUT|PATCH | `/api/v1/app/library/book-additions/{id}` |  |
| DELETE | `/api/v1/app/library/book-additions/{id}` |  |
| GET | `/api/v1/app/library/book-wise-transactions/pre-requisite` |  |
| GET | `/api/v1/app/library/book-wise-transactions` |  |
| POST | `/api/v1/app/library/book-wise-transactions` |  |
| GET | `/api/v1/app/library/book-wise-transactions/{id}` |  |
| PUT|PATCH | `/api/v1/app/library/book-wise-transactions/{id}` |  |
| DELETE | `/api/v1/app/library/book-wise-transactions/{id}` |  |
| GET | `/api/v1/app/library/transactions/pre-requisite` |  |
| GET | `/api/v1/app/library/transactions/action-pre-requisite` |  |
| POST | `/api/v1/app/library/transactions/{book_issue}/return` |  |
| GET | `/api/v1/app/library/transactions` |  |
| POST | `/api/v1/app/library/transactions` |  |
| GET | `/api/v1/app/library/transactions/{id}` |  |
| PUT|PATCH | `/api/v1/app/library/transactions/{id}` |  |
| DELETE | `/api/v1/app/library/transactions/{id}` |  |
| GET | `/api/v1/app/library/reports/top-borrower/pre-requisite` | library:report |
| GET | `/api/v1/app/library/reports/top-borrower` | library:report |
| GET | `/api/v1/app/library/reports/top-borrowed-book/pre-requisite` | library:report |
| GET | `/api/v1/app/library/reports/top-borrowed-book` | library:report |

## site  (31 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/site/pages/pre-requisite` |  |
| POST | `/api/v1/app/site/pages/{page}/assets/{type}` |  |
| DELETE | `/api/v1/app/site/pages/{page}/assets/{type}` |  |
| POST | `/api/v1/app/site/pages/{page}/blocks` |  |
| POST | `/api/v1/app/site/pages/{page}/slider` |  |
| POST | `/api/v1/app/site/pages/{page}/cta` |  |
| POST | `/api/v1/app/site/pages/{page}/meta` |  |
| GET | `/api/v1/app/site/pages` |  |
| POST | `/api/v1/app/site/pages` |  |
| GET | `/api/v1/app/site/pages/{id}` |  |
| PUT|PATCH | `/api/v1/app/site/pages/{id}` |  |
| DELETE | `/api/v1/app/site/pages/{id}` |  |
| GET | `/api/v1/app/site/menus/pre-requisite` |  |
| POST | `/api/v1/app/site/menus/reorder` |  |
| POST | `/api/v1/app/site/menus/reorder-sub-menu` |  |
| GET | `/api/v1/app/site/menus` |  |
| POST | `/api/v1/app/site/menus` |  |
| GET | `/api/v1/app/site/menus/{id}` |  |
| PUT|PATCH | `/api/v1/app/site/menus/{id}` |  |
| DELETE | `/api/v1/app/site/menus/{id}` |  |
| GET | `/api/v1/app/site/blocks/pre-requisite` |  |
| POST | `/api/v1/app/site/blocks/reorder` |  |
| POST | `/api/v1/app/site/blocks/{block}/assets/{type}` |  |
| DELETE | `/api/v1/app/site/blocks/{block}/assets/{type}` |  |
| POST | `/api/v1/app/site/blocks/{block}/slider-images` |  |
| DELETE | `/api/v1/app/site/blocks/{block}/slider-images/{image}` |  |
| GET | `/api/v1/app/site/blocks` |  |
| POST | `/api/v1/app/site/blocks` |  |
| GET | `/api/v1/app/site/blocks/{id}` |  |
| PUT|PATCH | `/api/v1/app/site/blocks/{id}` |  |
| DELETE | `/api/v1/app/site/blocks/{id}` |  |

## hostel  (30 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/hostel/blocks/pre-requisite` | hostel:manage |
| GET | `/api/v1/app/hostel/blocks` | hostel:manage |
| POST | `/api/v1/app/hostel/blocks` | hostel:manage |
| GET | `/api/v1/app/hostel/blocks/{id}` | hostel:manage |
| PUT|PATCH | `/api/v1/app/hostel/blocks/{id}` | hostel:manage |
| DELETE | `/api/v1/app/hostel/blocks/{id}` | hostel:manage |
| GET | `/api/v1/app/hostel/block-incharges/pre-requisite` | hostel:manage |
| GET | `/api/v1/app/hostel/block-incharges` | hostel:manage |
| POST | `/api/v1/app/hostel/block-incharges` | hostel:manage |
| GET | `/api/v1/app/hostel/block-incharges/{id}` | hostel:manage |
| PUT|PATCH | `/api/v1/app/hostel/block-incharges/{id}` | hostel:manage |
| DELETE | `/api/v1/app/hostel/block-incharges/{id}` | hostel:manage |
| GET | `/api/v1/app/hostel/floors/pre-requisite` | hostel:manage |
| GET | `/api/v1/app/hostel/floors` | hostel:manage |
| POST | `/api/v1/app/hostel/floors` | hostel:manage |
| GET | `/api/v1/app/hostel/floors/{id}` | hostel:manage |
| PUT|PATCH | `/api/v1/app/hostel/floors/{id}` | hostel:manage |
| DELETE | `/api/v1/app/hostel/floors/{id}` | hostel:manage |
| GET | `/api/v1/app/hostel/rooms/pre-requisite` | hostel:manage |
| GET | `/api/v1/app/hostel/rooms` | hostel:manage |
| POST | `/api/v1/app/hostel/rooms` | hostel:manage |
| GET | `/api/v1/app/hostel/rooms/{id}` | hostel:manage |
| PUT|PATCH | `/api/v1/app/hostel/rooms/{id}` | hostel:manage |
| DELETE | `/api/v1/app/hostel/rooms/{id}` | hostel:manage |
| GET | `/api/v1/app/hostel/room-allocations/pre-requisite` | hostel:manage |
| GET | `/api/v1/app/hostel/room-allocations` | hostel:manage |
| POST | `/api/v1/app/hostel/room-allocations` | hostel:manage |
| GET | `/api/v1/app/hostel/room-allocations/{id}` | hostel:manage |
| PUT|PATCH | `/api/v1/app/hostel/room-allocations/{id}` | hostel:manage |
| DELETE | `/api/v1/app/hostel/room-allocations/{id}` | hostel:manage |

## task  (30 endpoints)

| Method | URI | Permission |
|---|---|---|
| POST | `/api/v1/app/tasks/{task}/tags` |  |
| POST | `/api/v1/app/tasks/{task}/favorite` |  |
| POST | `/api/v1/app/tasks/{task}/status` |  |
| POST | `/api/v1/app/tasks/{task}/media` |  |
| DELETE | `/api/v1/app/tasks/{task}/media/{uuid}` |  |
| GET | `/api/v1/app/tasks/{task}/repeat/pre-requisite` |  |
| POST | `/api/v1/app/tasks/{task}/repeat` |  |
| POST | `/api/v1/app/tasks/reorder` |  |
| POST | `/api/v1/app/tasks/lists/move` |  |
| GET | `/api/v1/app/tasks/pre-requisite` |  |
| GET | `/api/v1/app/tasks` |  |
| POST | `/api/v1/app/tasks` |  |
| GET | `/api/v1/app/tasks/{id}` |  |
| PUT|PATCH | `/api/v1/app/tasks/{id}` |  |
| DELETE | `/api/v1/app/tasks/{id}` |  |
| POST | `/api/v1/app/tasks/{task}/checklists/{checklist}/status` |  |
| GET | `/api/v1/app/tasks.checklists` |  |
| POST | `/api/v1/app/tasks.checklists` |  |
| GET | `/api/v1/app/tasks.checklists/{id}` |  |
| PUT|PATCH | `/api/v1/app/tasks.checklists/{id}` |  |
| DELETE | `/api/v1/app/tasks.checklists/{id}` |  |
| GET | `/api/v1/app/tasks.members` |  |
| POST | `/api/v1/app/tasks.members` |  |
| GET | `/api/v1/app/tasks.members/{id}` |  |
| PUT|PATCH | `/api/v1/app/tasks.members/{id}` |  |
| DELETE | `/api/v1/app/tasks.members/{id}` |  |
| GET | `/api/v1/app/tasks/dashboard/stat` | task:read |
| GET | `/api/v1/app/tasks/dashboard/favorite` | task:read |
| GET | `/api/v1/app/tasks/dashboard/chart` | task:read |
| GET | `/api/v1/app/tasks/dashboard/record` | task:read |

## communication  (26 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/communication/announcements/pre-requisite` |  |
| POST | `/api/v1/app/communication/announcements/{announcement}/pin` |  |
| POST | `/api/v1/app/communication/announcements/{announcement}/unpin` |  |
| POST | `/api/v1/app/communication/announcements/{announcement}/show-as-popup` |  |
| GET | `/api/v1/app/communication/announcements` |  |
| POST | `/api/v1/app/communication/announcements` |  |
| GET | `/api/v1/app/communication/announcements/{id}` |  |
| PUT|PATCH | `/api/v1/app/communication/announcements/{id}` |  |
| DELETE | `/api/v1/app/communication/announcements/{id}` |  |
| GET | `/api/v1/app/communication/emails/pre-requisite` |  |
| GET | `/api/v1/app/communication/emails` |  |
| POST | `/api/v1/app/communication/emails` |  |
| GET | `/api/v1/app/communication/emails/{id}` |  |
| GET | `/api/v1/app/communication/sms/pre-requisite` |  |
| GET | `/api/v1/app/communication/sms` |  |
| POST | `/api/v1/app/communication/sms` |  |
| GET | `/api/v1/app/communication/sms/{id}` |  |
| GET | `/api/v1/app/communication/whatsapp/pre-requisite` |  |
| GET | `/api/v1/app/communication/whatsapp` |  |
| POST | `/api/v1/app/communication/whatsapp` |  |
| GET | `/api/v1/app/communication/whatsapp/{id}` |  |
| GET | `/api/v1/app/communication/push-messages/pre-requisite` |  |
| POST | `/api/v1/app/communication/push-messages/send-test-notification` |  |
| GET | `/api/v1/app/communication/push-messages` |  |
| POST | `/api/v1/app/communication/push-messages` |  |
| GET | `/api/v1/app/communication/push-messages/{id}` |  |

## helpdesk  (21 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/helpdesk/faqs/pre-requisite` |  |
| POST | `/api/v1/app/helpdesk/faqs/delete` |  |
| GET | `/api/v1/app/helpdesk/faqs` |  |
| POST | `/api/v1/app/helpdesk/faqs` |  |
| GET | `/api/v1/app/helpdesk/faqs/{id}` |  |
| PUT|PATCH | `/api/v1/app/helpdesk/faqs/{id}` |  |
| DELETE | `/api/v1/app/helpdesk/faqs/{id}` |  |
| GET | `/api/v1/app/helpdesk/tickets/pre-requisite` |  |
| POST | `/api/v1/app/helpdesk/tickets/delete` |  |
| POST | `/api/v1/app/helpdesk/tickets/{ticket}/assign` |  |
| POST | `/api/v1/app/helpdesk/tickets/{ticket}/unassign/{employee}` |  |
| POST | `/api/v1/app/helpdesk/tickets/assign` |  |
| POST | `/api/v1/app/helpdesk/tickets/category` |  |
| POST | `/api/v1/app/helpdesk/tickets/priority` |  |
| POST | `/api/v1/app/helpdesk/tickets/{ticket}/messages` |  |
| POST | `/api/v1/app/helpdesk/tickets/{ticket}/messages/{message}` |  |
| GET | `/api/v1/app/helpdesk/tickets` |  |
| POST | `/api/v1/app/helpdesk/tickets` |  |
| GET | `/api/v1/app/helpdesk/tickets/{id}` |  |
| PUT|PATCH | `/api/v1/app/helpdesk/tickets/{id}` |  |
| DELETE | `/api/v1/app/helpdesk/tickets/{id}` |  |

## approval  (19 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/approval/types/pre-requisite` |  |
| GET | `/api/v1/app/approval/types` |  |
| POST | `/api/v1/app/approval/types` |  |
| GET | `/api/v1/app/approval/types/{id}` |  |
| PUT|PATCH | `/api/v1/app/approval/types/{id}` |  |
| DELETE | `/api/v1/app/approval/types/{id}` |  |
| GET | `/api/v1/app/approval/requests/pre-requisite` |  |
| GET | `/api/v1/app/approval/requests/{approval_request}/action/pre-requisite` |  |
| POST | `/api/v1/app/approval/requests/{approval_request}/status` |  |
| POST | `/api/v1/app/approval/requests/{approval_request}/cancel` |  |
| POST | `/api/v1/app/approval/requests/{approval_request}/media` |  |
| DELETE | `/api/v1/app/approval/requests/{approval_request}/media/{uuid}` |  |
| GET | `/api/v1/app/approval/requests` |  |
| POST | `/api/v1/app/approval/requests` |  |
| GET | `/api/v1/app/approval/requests/{id}` |  |
| PUT|PATCH | `/api/v1/app/approval/requests/{id}` |  |
| DELETE | `/api/v1/app/approval/requests/{id}` |  |
| GET | `/api/v1/app/approval/reports/request-summary/pre-requisite` | approval:report |
| GET | `/api/v1/app/approval/reports/request-summary` | approval:report |

## asset  (18 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/asset/building/blocks/pre-requisite` | building:manage |
| GET | `/api/v1/app/asset/building/blocks` | building:manage |
| POST | `/api/v1/app/asset/building/blocks` | building:manage |
| GET | `/api/v1/app/asset/building/blocks/{id}` | building:manage |
| PUT|PATCH | `/api/v1/app/asset/building/blocks/{id}` | building:manage |
| DELETE | `/api/v1/app/asset/building/blocks/{id}` | building:manage |
| GET | `/api/v1/app/asset/building/floors/pre-requisite` | building:manage |
| GET | `/api/v1/app/asset/building/floors` | building:manage |
| POST | `/api/v1/app/asset/building/floors` | building:manage |
| GET | `/api/v1/app/asset/building/floors/{id}` | building:manage |
| PUT|PATCH | `/api/v1/app/asset/building/floors/{id}` | building:manage |
| DELETE | `/api/v1/app/asset/building/floors/{id}` | building:manage |
| GET | `/api/v1/app/asset/building/rooms/pre-requisite` | building:manage |
| GET | `/api/v1/app/asset/building/rooms` | building:manage |
| POST | `/api/v1/app/asset/building/rooms` | building:manage |
| GET | `/api/v1/app/asset/building/rooms/{id}` | building:manage |
| PUT|PATCH | `/api/v1/app/asset/building/rooms/{id}` | building:manage |
| DELETE | `/api/v1/app/asset/building/rooms/{id}` | building:manage |

## calendar  (18 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/calendar/holidays/pre-requisite` |  |
| GET | `/api/v1/app/calendar/holidays` |  |
| POST | `/api/v1/app/calendar/holidays` |  |
| GET | `/api/v1/app/calendar/holidays/{id}` |  |
| PUT|PATCH | `/api/v1/app/calendar/holidays/{id}` |  |
| DELETE | `/api/v1/app/calendar/holidays/{id}` |  |
| GET | `/api/v1/app/calendar/celebrations/pre-requisite` |  |
| GET | `/api/v1/app/calendar/celebrations` | celebration:read |
| GET | `/api/v1/app/calendar/events/pre-requisite` |  |
| POST | `/api/v1/app/calendar/events/{event}/assets/{type}` |  |
| DELETE | `/api/v1/app/calendar/events/{event}/assets/{type}` |  |
| POST | `/api/v1/app/calendar/events/{event}/pin` |  |
| POST | `/api/v1/app/calendar/events/{event}/unpin` |  |
| GET | `/api/v1/app/calendar/events` |  |
| POST | `/api/v1/app/calendar/events` |  |
| GET | `/api/v1/app/calendar/events/{id}` |  |
| PUT|PATCH | `/api/v1/app/calendar/events/{id}` |  |
| DELETE | `/api/v1/app/calendar/events/{id}` |  |

## mess  (18 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/mess/menu-items/pre-requisite` | menu-item:manage |
| GET | `/api/v1/app/mess/menu-items` | menu-item:manage |
| POST | `/api/v1/app/mess/menu-items` | menu-item:manage |
| GET | `/api/v1/app/mess/menu-items/{id}` | menu-item:manage |
| PUT|PATCH | `/api/v1/app/mess/menu-items/{id}` | menu-item:manage |
| DELETE | `/api/v1/app/mess/menu-items/{id}` | menu-item:manage |
| GET | `/api/v1/app/mess/meals/pre-requisite` | meal:manage |
| GET | `/api/v1/app/mess/meals` | meal:manage |
| POST | `/api/v1/app/mess/meals` | meal:manage |
| GET | `/api/v1/app/mess/meals/{id}` | meal:manage |
| PUT|PATCH | `/api/v1/app/mess/meals/{id}` | meal:manage |
| DELETE | `/api/v1/app/mess/meals/{id}` | meal:manage |
| GET | `/api/v1/app/mess/meal-logs/pre-requisite` |  |
| GET | `/api/v1/app/mess/meal-logs` |  |
| POST | `/api/v1/app/mess/meal-logs` |  |
| GET | `/api/v1/app/mess/meal-logs/{id}` |  |
| PUT|PATCH | `/api/v1/app/mess/meal-logs/{id}` |  |
| DELETE | `/api/v1/app/mess/meal-logs/{id}` |  |

## contact  (17 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/contact/config/document-types` | contact:config |
| POST | `/api/v1/app/contact/config/document-types` | contact:config |
| GET | `/api/v1/app/contact/config/document-types/{id}` | contact:config |
| PUT|PATCH | `/api/v1/app/contact/config/document-types/{id}` | contact:config |
| DELETE | `/api/v1/app/contact/config/document-types/{id}` | contact:config |
| POST | `/api/v1/app/contacts/{contact}/user/confirm` |  |
| GET | `/api/v1/app/contacts/{contact}/user` |  |
| POST | `/api/v1/app/contacts/{contact}/user` |  |
| PATCH | `/api/v1/app/contacts/{contact}/user` |  |
| POST | `/api/v1/app/contacts/{contact}/photo` |  |
| DELETE | `/api/v1/app/contacts/{contact}/photo` |  |
| GET | `/api/v1/app/contacts/pre-requisite` |  |
| GET | `/api/v1/app/contacts` |  |
| POST | `/api/v1/app/contacts` |  |
| GET | `/api/v1/app/contacts/{id}` |  |
| PUT|PATCH | `/api/v1/app/contacts/{id}` |  |
| DELETE | `/api/v1/app/contacts/{id}` |  |

## blog  (16 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/blogs/pre-requisite` |  |
| POST | `/api/v1/app/blogs/{blog}/assets/{type}` |  |
| DELETE | `/api/v1/app/blogs/{blog}/assets/{type}` |  |
| POST | `/api/v1/app/blogs/{blog}/meta` |  |
| POST | `/api/v1/app/blogs/{blog}/archive` |  |
| POST | `/api/v1/app/blogs/{blog}/unarchive` |  |
| POST | `/api/v1/app/blogs/{blog}/pin` |  |
| POST | `/api/v1/app/blogs/{blog}/unpin` |  |
| POST | `/api/v1/app/blogs/archive` |  |
| POST | `/api/v1/app/blogs/unarchive` |  |
| POST | `/api/v1/app/blogs/delete` |  |
| GET | `/api/v1/app/blogs` |  |
| POST | `/api/v1/app/blogs` |  |
| GET | `/api/v1/app/blogs/{id}` |  |
| PUT|PATCH | `/api/v1/app/blogs/{id}` |  |
| DELETE | `/api/v1/app/blogs/{id}` |  |

## news  (16 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/news/pre-requisite` |  |
| POST | `/api/v1/app/news/{news}/assets/{type}` |  |
| DELETE | `/api/v1/app/news/{news}/assets/{type}` |  |
| POST | `/api/v1/app/news/{news}/meta` |  |
| POST | `/api/v1/app/news/{news}/archive` |  |
| POST | `/api/v1/app/news/{news}/unarchive` |  |
| POST | `/api/v1/app/news/{news}/pin` |  |
| POST | `/api/v1/app/news/{news}/unpin` |  |
| POST | `/api/v1/app/news/archive` |  |
| POST | `/api/v1/app/news/unarchive` |  |
| POST | `/api/v1/app/news/delete` |  |
| GET | `/api/v1/app/news` |  |
| POST | `/api/v1/app/news` |  |
| GET | `/api/v1/app/news/{id}` |  |
| PUT|PATCH | `/api/v1/app/news/{id}` |  |
| DELETE | `/api/v1/app/news/{id}` |  |

## auth  (16 endpoints)

| Method | URI | Permission |
|---|---|---|
| POST | `/api/v1/app/login` |  |
| POST | `/api/v1/app/login/otp/request` |  |
| POST | `/api/v1/app/login/otp/confirm` |  |
| POST | `/api/v1/app/password/request` |  |
| POST | `/api/v1/app/password/confirm` |  |
| POST | `/api/v1/app/password/reset` |  |
| POST | `/api/v1/app/register` |  |
| POST | `/api/v1/app/register/email` |  |
| POST | `/api/v1/app/register/verify` |  |
| POST | `/api/v1/app/logout` |  |
| POST | `/api/v1/app/security` |  |
| POST | `/api/v1/app/unlock` |  |
| GET | `/api/v1/app/user` |  |
| POST | `/api/v1/app/confirm-password` |  |
| GET | `/api/v1/app/config` |  |
| POST | `/api/v1/app/lock` |  |

## activity  (15 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/activity/trips/pre-requisite` |  |
| POST | `/api/v1/app/activity/trips/{trip}/assets/{type}` |  |
| DELETE | `/api/v1/app/activity/trips/{trip}/assets/{type}` |  |
| POST | `/api/v1/app/activity/trips/{trip}/media` |  |
| DELETE | `/api/v1/app/activity/trips/{trip}/media/{uuid}` |  |
| GET | `/api/v1/app/activity/trips.participants` |  |
| POST | `/api/v1/app/activity/trips.participants` |  |
| GET | `/api/v1/app/activity/trips.participants/{id}` |  |
| PUT|PATCH | `/api/v1/app/activity/trips.participants/{id}` |  |
| DELETE | `/api/v1/app/activity/trips.participants/{id}` |  |
| GET | `/api/v1/app/activity/trips` |  |
| POST | `/api/v1/app/activity/trips` |  |
| GET | `/api/v1/app/activity/trips/{id}` |  |
| PUT|PATCH | `/api/v1/app/activity/trips/{id}` |  |
| DELETE | `/api/v1/app/activity/trips/{id}` |  |

## guardian  (13 endpoints)

| Method | URI | Permission |
|---|---|---|
| POST | `/api/v1/app/guardians/{guardian}/user/confirm` |  |
| GET | `/api/v1/app/guardians/{guardian}/user` |  |
| POST | `/api/v1/app/guardians/{guardian}/user` |  |
| PATCH | `/api/v1/app/guardians/{guardian}/user` |  |
| POST | `/api/v1/app/guardians/{guardian}/period` |  |
| POST | `/api/v1/app/guardians/{guardian}/photo` |  |
| DELETE | `/api/v1/app/guardians/{guardian}/photo` |  |
| GET | `/api/v1/app/guardians/pre-requisite` |  |
| POST | `/api/v1/app/guardians/import` | guardian:create |
| GET | `/api/v1/app/guardians` |  |
| GET | `/api/v1/app/guardians/{id}` |  |
| PUT|PATCH | `/api/v1/app/guardians/{id}` |  |
| DELETE | `/api/v1/app/guardians/{id}` |  |

## form  (12 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/forms/pre-requisite` |  |
| POST | `/api/v1/app/forms/{form}/status` |  |
| GET | `/api/v1/app/forms/{form}/detail` |  |
| POST | `/api/v1/app/forms/{form}/submit` |  |
| GET | `/api/v1/app/forms.submissions` | form-submission:manage |
| GET | `/api/v1/app/forms.submissions/{id}` | form-submission:manage |
| DELETE | `/api/v1/app/forms.submissions/{id}` | form-submission:manage |
| GET | `/api/v1/app/forms` |  |
| POST | `/api/v1/app/forms` |  |
| GET | `/api/v1/app/forms/{id}` |  |
| PUT|PATCH | `/api/v1/app/forms/{id}` |  |
| DELETE | `/api/v1/app/forms/{id}` |  |

## recruitment  (12 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/recruitment/vacancies/pre-requisite` |  |
| GET | `/api/v1/app/recruitment/vacancies` |  |
| POST | `/api/v1/app/recruitment/vacancies` |  |
| GET | `/api/v1/app/recruitment/vacancies/{id}` |  |
| PUT|PATCH | `/api/v1/app/recruitment/vacancies/{id}` |  |
| DELETE | `/api/v1/app/recruitment/vacancies/{id}` |  |
| GET | `/api/v1/app/recruitment/applications/pre-requisite` |  |
| GET | `/api/v1/app/recruitment/applications` |  |
| POST | `/api/v1/app/recruitment/applications` |  |
| GET | `/api/v1/app/recruitment/applications/{id}` |  |
| PUT|PATCH | `/api/v1/app/recruitment/applications/{id}` |  |
| DELETE | `/api/v1/app/recruitment/applications/{id}` |  |

## post  (10 endpoints)

| Method | URI | Permission |
|---|---|---|
| POST | `/api/v1/app/post/images` |  |
| DELETE | `/api/v1/app/post/images` |  |
| POST | `/api/v1/app/posts/{post}/pin` |  |
| POST | `/api/v1/app/posts/{post}/unpin` |  |
| GET | `/api/v1/app/posts/pre-requisite` |  |
| GET | `/api/v1/app/posts` |  |
| POST | `/api/v1/app/posts` |  |
| GET | `/api/v1/app/posts/{id}` |  |
| PUT|PATCH | `/api/v1/app/posts/{id}` |  |
| DELETE | `/api/v1/app/posts/{id}` |  |

## gallery  (9 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/galleries/pre-requisite` |  |
| POST | `/api/v1/app/galleries/{gallery}/upload` |  |
| POST | `/api/v1/app/galleries/{gallery}/images/{image}/cover` |  |
| DELETE | `/api/v1/app/galleries/{gallery}/images/{image}` |  |
| GET | `/api/v1/app/galleries` |  |
| POST | `/api/v1/app/galleries` |  |
| GET | `/api/v1/app/galleries/{id}` |  |
| PUT|PATCH | `/api/v1/app/galleries/{id}` |  |
| DELETE | `/api/v1/app/galleries/{id}` |  |

## chat  (8 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/users` |  |
| GET | `/api/v1/app/` |  |
| POST | `/api/v1/app/` |  |
| GET | `/api/v1/app/{chat}` |  |
| POST | `/api/v1/app/{chat}/read` |  |
| DELETE | `/api/v1/app/{chat}` |  |
| GET | `/api/v1/app/{chat}/messages` |  |
| POST | `/api/v1/app/{chat}/messages` |  |

## discipline  (6 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/discipline/incidents/pre-requisite` |  |
| GET | `/api/v1/app/discipline/incidents` | incident:manage |
| POST | `/api/v1/app/discipline/incidents` | incident:manage |
| GET | `/api/v1/app/discipline/incidents/{id}` | incident:manage |
| PUT|PATCH | `/api/v1/app/discipline/incidents/{id}` | incident:manage |
| DELETE | `/api/v1/app/discipline/incidents/{id}` | incident:manage |

## device  (5 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/devices` |  |
| POST | `/api/v1/app/devices` |  |
| GET | `/api/v1/app/devices/{id}` |  |
| PUT|PATCH | `/api/v1/app/devices/{id}` |  |
| DELETE | `/api/v1/app/devices/{id}` |  |

## misc  (4 endpoints)

| Method | URI | Permission |
|---|---|---|
| GET | `/api/v1/app/suggestions/institutes` |  |
| GET | `/api/v1/app/suggestions/affiliation-bodies` |  |
| GET | `/api/v1/app/suggestions/units` |  |
| GET | `/api/v1/app/suggestions/library/books` |  |
