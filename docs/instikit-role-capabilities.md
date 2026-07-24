# InstiKit — Role Capabilities (17 roles)

Auto-generated from `resources/var/permission.json`. `admin` implicitly holds every permission.

| Role | # permissions |
|---|---|
| admin | 644 |
| observer | 104 |
| manager | 567 |
| principal | 468 |
| staff | 80 |
| accountant | 73 |
| librarian | 35 |
| exam-incharge | 38 |
| transport-incharge | 71 |
| inventory-incharge | 58 |
| mess-incharge | 16 |
| hostel-incharge | 15 |
| attendance-assistant | 2 |
| receptionist | 68 |
| student | 29 |
| guardian | 29 |
| user | 0 |

---

## admin  (644 permissions)

- **academic**: academic-department:manage, academic:admin-access, academic:config, academic:incharge-access, batch-incharge:create, batch-incharge:delete, batch-incharge:edit, batch-incharge:export, batch-incharge:read, batch:create, batch:delete, batch:edit, batch:export, batch:read, book-list:manage, certificate-template:create, certificate-template:delete, certificate-template:edit, certificate-template:export, certificate-template:read, certificate:create, certificate:delete, certificate:edit, certificate:export, certificate:read, class-timing:create, class-timing:delete, class-timing:edit, class-timing:export, class-timing:read, course-incharge:create, course-incharge:delete, course-incharge:edit, course-incharge:export, course-incharge:read, course:create, course:delete, course:edit, course:export, course:read, division-incharge:create, division-incharge:delete, division-incharge:edit, division-incharge:export, division-incharge:read, division:create, division:delete, division:edit, division:export, division:read, id-card:manage, period:change, period:config, period:create, period:delete, period:edit, period:export, period:read, program:manage, session:manage, subject-incharge:create, subject-incharge:delete, subject-incharge:edit, subject-incharge:export, subject-incharge:read, subject:create, subject:delete, subject:edit, subject:export, subject:read, timetable:allocate, timetable:create, timetable:delete, timetable:edit, timetable:export, timetable:read
- **activity**: activity:config, trip-participant:manage, trip:manage, trip:read
- **approval**: approval-request:action, approval-request:create, approval-request:delete, approval-request:edit, approval-request:export, approval-request:read, approval-type:manage, approval:config, approval:report
- **asset**: asset:config, building:manage
- **blog**: blog:config, blog:create, blog:delete, blog:edit, blog:export, blog:read
- **calendar**: calendar:config, celebration:export, celebration:read, event:admin-access, event:create, event:delete, event:edit, event:export, event:read, holiday:create, holiday:delete, holiday:edit, holiday:export, holiday:read
- **communication**: announcement:admin-access, announcement:create, announcement:delete, announcement:edit, announcement:export, announcement:read, announcement:view-log, communication:config, email:read, email:send, push-message:read, push-message:send, sms:read, sms:send, whatsapp:read, whatsapp:send
- **config**: activity-log:export, activity-log:manage, backup:manage, config:store, locale:create, locale:delete, locale:edit, locale:read
- **contact**: contact:config, contact:create, contact:delete, contact:edit, contact:export, contact:read
- **custom_field**: custom-field:manage
- **dashboard**: dashboard:stat
- **discipline**: discipline:config, incident:manage
- **employee**: attendance:admin-access, attendance:config, attendance:export, attendance:mark, attendance:read, department:create, department:delete, department:edit, department:export, department:read, designation:admin-access, designation:create, designation:delete, designation:edit, designation:export, designation:read, designation:self-access, designation:subordinate-access, employee-record:manage, employee:config, employee:create, employee:delete, employee:dialogue, employee:edit, employee:edit-request-action, employee:export, employee:read, employee:self-service, employee:self-service-action, employee:summary, employment-record:manage, leave-allocation:admin-access, leave-allocation:create, leave-allocation:delete, leave-allocation:edit, leave-allocation:export, leave-allocation:read, leave-request:action, leave-request:admin-access, leave-request:create, leave-request:delete, leave-request:edit, leave-request:export, leave-request:read, leave:config, payroll:admin-access, payroll:config, payroll:create, payroll:delete, payroll:edit, payroll:export, payroll:process, payroll:read, salary-structure:create, salary-structure:delete, salary-structure:edit, salary-structure:export, salary-structure:read, salary-template:create, salary-template:delete, salary-template:edit, salary-template:export, salary-template:read, timesheet:create, timesheet:delete, timesheet:edit, timesheet:export, timesheet:import, timesheet:read, timesheet:sync, work-shift:assign, work-shift:create, work-shift:delete, work-shift:edit, work-shift:export, work-shift:read
- **exam**: exam-admit-card:access, exam-assessment:manage, exam-competency:manage, exam-form:manage, exam-grade:manage, exam-marksheet:access, exam-marksheet:process, exam-observation:manage, exam-schedule:create, exam-schedule:delete, exam-schedule:edit, exam-schedule:export, exam-schedule:read, exam-term:manage, exam:config, exam:manage, exam:marks-record, exam:report, exam:subject-incharge-wise-marks-record, online-exam:create, online-exam:delete, online-exam:edit, online-exam:export, online-exam:read
- **finance**: day-closure:manage, fee-component:manage, fee-concession:create, fee-concession:delete, fee-concession:edit, fee-concession:export, fee-concession:read, fee-group:create, fee-group:delete, fee-group:edit, fee-group:export, fee-group:read, fee-head:create, fee-head:delete, fee-head:edit, fee-head:export, fee-head:read, fee-payment:import, fee-structure:allocate, fee-structure:create, fee-structure:delete, fee-structure:edit, fee-structure:export, fee-structure:read, finance:config, finance:export, finance:report, ledger-type:create, ledger-type:delete, ledger-type:edit, ledger-type:export, ledger-type:read, ledger:create, ledger:delete, ledger:edit, ledger:export, ledger:read, receipt:cancel, receipt:create, receipt:delete, receipt:edit, receipt:export, receipt:read, transaction:cancel, transaction:config, transaction:create, transaction:delete, transaction:edit, transaction:export, transaction:manage-clearance, transaction:read
- **form**: form-submission:manage, form:config, form:create, form:delete, form:edit, form:export, form:read, form:submit
- **gallery**: gallery:config, gallery:create, gallery:delete, gallery:edit, gallery:export, gallery:read
- **general**: access:reports, chat:access, login:action, password:update, post:comment, post:config, post:create, post:delete, post:edit, post:read, profile:update
- **guardian**: guardian:config, guardian:create, guardian:delete, guardian:edit, guardian:export, guardian:read
- **helpdesk**: faq:create, faq:delete, faq:edit, faq:export, faq:read, helpdesk:config, ticket:action, ticket:create, ticket:delete, ticket:edit, ticket:export, ticket:read
- **hostel**: hostel-incharge:create, hostel-incharge:delete, hostel-incharge:edit, hostel-incharge:export, hostel-incharge:read, hostel-room-allocation:create, hostel-room-allocation:delete, hostel-room-allocation:edit, hostel-room-allocation:export, hostel-room-allocation:read, hostel:manage
- **inventory**: inventory:admin-access, inventory:config, inventory:report, stock-adjustment:create, stock-adjustment:delete, stock-adjustment:edit, stock-adjustment:export, stock-adjustment:read, stock-category:create, stock-category:delete, stock-category:edit, stock-category:export, stock-category:read, stock-item:create, stock-item:delete, stock-item:edit, stock-item:export, stock-item:read, stock-purchase:create, stock-purchase:delete, stock-purchase:edit, stock-purchase:export, stock-purchase:read, stock-requisition:create, stock-requisition:delete, stock-requisition:edit, stock-requisition:export, stock-requisition:read, stock-return:create, stock-return:delete, stock-return:edit, stock-return:export, stock-return:read, stock-transfer:create, stock-transfer:delete, stock-transfer:edit, stock-transfer:export, stock-transfer:read, vendor:create, vendor:delete, vendor:edit, vendor:export, vendor:read
- **library**: book-addition:create, book-addition:delete, book-addition:edit, book-addition:export, book-addition:read, book:create, book:delete, book:edit, book:export, book:issue, book:read, book:return, library:config, library:report
- **mess**: meal-log:create, meal-log:delete, meal-log:edit, meal-log:export, meal-log:read, meal:manage, menu-item:manage, mess:config
- **news**: news:config, news:create, news:delete, news:edit, news:export, news:read
- **reception**: call-log:create, call-log:delete, call-log:edit, call-log:export, call-log:read, complaint:action, complaint:admin-access, complaint:create, complaint:delete, complaint:edit, complaint:export, complaint:read, correspondence:create, correspondence:delete, correspondence:edit, correspondence:export, correspondence:read, enquiry:action, enquiry:admin-access, enquiry:create, enquiry:delete, enquiry:edit, enquiry:export, enquiry:follow-up, enquiry:read, gate-pass:create, gate-pass:delete, gate-pass:edit, gate-pass:export, gate-pass:read, query:action, query:delete, query:read, reception:config, visitor-log:create, visitor-log:delete, visitor-log:edit, visitor-log:export, visitor-log:read
- **recruitment**: job-application:create, job-application:delete, job-application:edit, job-application:export, job-application:read, job-vacancy:create, job-vacancy:delete, job-vacancy:edit, job-vacancy:export, job-vacancy:read, recruitment:config
- **resource**: assignment:create, assignment:delete, assignment:edit, assignment:export, assignment:read, assignment:view-log, book-list:read, download:create, download:delete, download:edit, download:export, download:read, learning-material:create, learning-material:delete, learning-material:edit, learning-material:export, learning-material:read, learning-material:view-log, lesson-plan:create, lesson-plan:delete, lesson-plan:edit, lesson-plan:export, lesson-plan:read, online-class:create, online-class:delete, online-class:edit, online-class:export, online-class:read, resource:config, resource:report, student-diary:create, student-diary:delete, student-diary:edit, student-diary:export, student-diary:read, student-diary:view-log, syllabus:create, syllabus:delete, syllabus:edit, syllabus:export, syllabus:read
- **site**: site:manage
- **student**: alumni:read, fee:bank-transfer, fee:bank-transfer-action, fee:cancel-payment, fee:change-payment-date, fee:custom-concession, fee:customize-late-fee, fee:edit, fee:edit-payment, fee:flexible-installment-payment, fee:head-wise-payment, fee:manage-force-custom-fee, fee:multiple-installment-payment, fee:partial-payment, fee:payment, fee:reset, fee:set, registration:action, registration:create, registration:delete, registration:edit, registration:export, registration:fee, registration:read, registration:verify, student-record:cancel, student-record:manage, student:admin-access, student:config, student:create, student:delete, student:dialogue, student:edit, student:edit-request-action, student:export, student:incharge-access, student:incharge-wise-mark-attendance, student:leave-request, student:list-attendance, student:manage-timesheet, student:mark-attendance, student:promotion, student:read, student:report, student:self-access, student:service-allocation, student:service-request, student:service-request-action, student:summary, student:transfer, student:transfer-request, student:transfer-request-action
- **task**: task:config, task:create, task:delete, task:edit, task:export, task:read
- **team**: organization:manage, team:manage
- **transport**: transport-circle:create, transport-circle:delete, transport-circle:edit, transport-circle:export, transport-circle:read, transport-fee:create, transport-fee:delete, transport-fee:edit, transport-fee:export, transport-fee:read, transport-route:create, transport-route:delete, transport-route:edit, transport-route:export, transport-route:read, transport-stoppage:manage, transport:config, transport:export, transport:report, vehicle-case-record:create, vehicle-case-record:delete, vehicle-case-record:edit, vehicle-case-record:export, vehicle-case-record:read, vehicle-document:create, vehicle-document:delete, vehicle-document:edit, vehicle-document:export, vehicle-document:read, vehicle-expense-record:create, vehicle-expense-record:delete, vehicle-expense-record:edit, vehicle-expense-record:export, vehicle-expense-record:read, vehicle-fuel-record:create, vehicle-fuel-record:delete, vehicle-fuel-record:edit, vehicle-fuel-record:export, vehicle-fuel-record:read, vehicle-incharge:create, vehicle-incharge:delete, vehicle-incharge:edit, vehicle-incharge:export, vehicle-incharge:read, vehicle-service-record:create, vehicle-service-record:delete, vehicle-service-record:edit, vehicle-service-record:export, vehicle-service-record:read, vehicle-trip-record:create, vehicle-trip-record:delete, vehicle-trip-record:edit, vehicle-trip-record:export, vehicle-trip-record:read, vehicle:config, vehicle:create, vehicle:delete, vehicle:edit, vehicle:export, vehicle:read
- **user**: user:change-role, user:create, user:delete, user:edit, user:export, user:force-change-password, user:impersonate, user:read
- **utility**: todo:export, todo:manage, utility:config

## observer  (104 permissions)

- **academic**: batch-incharge:read, batch:read, certificate-template:read, certificate:read, class-timing:read, course-incharge:read, course:read, division-incharge:read, division:read, period:read, subject-incharge:read, subject:read, timetable:read
- **activity**: trip:read
- **approval**: approval-request:read, approval:report
- **calendar**: celebration:read, event:read, holiday:read
- **communication**: announcement:read
- **contact**: contact:read
- **dashboard**: dashboard:stat
- **employee**: attendance:read, department:read, designation:read, employee:read, leave-allocation:read, leave-request:read, payroll:read, salary-structure:read, salary-template:read, timesheet:read, work-shift:read
- **exam**: exam-schedule:read, exam:report, online-exam:read
- **finance**: fee-concession:read, fee-group:read, fee-head:read, fee-structure:read, finance:report, ledger-type:read, ledger:read, receipt:read, transaction:read
- **form**: form:read
- **gallery**: gallery:read
- **general**: access:reports, chat:access, login:action, password:update, post:read
- **guardian**: guardian:read
- **helpdesk**: faq:read, ticket:read
- **hostel**: hostel-incharge:read, hostel-room-allocation:read
- **inventory**: inventory:report, stock-adjustment:read, stock-category:read, stock-item:read, stock-purchase:read, stock-requisition:read, stock-return:read, stock-transfer:read, vendor:read
- **library**: book-addition:read, book:read, library:report
- **mess**: meal-log:read
- **reception**: call-log:read, complaint:read, correspondence:read, enquiry:read, gate-pass:read, query:read, visitor-log:read
- **recruitment**: job-application:read, job-vacancy:read
- **resource**: assignment:read, download:read, learning-material:read, lesson-plan:read, online-class:read, resource:report, student-diary:read, syllabus:read
- **student**: alumni:read, registration:read, student:read, student:report
- **task**: task:read
- **transport**: transport-circle:read, transport-fee:read, transport-route:read, transport:report, vehicle-case-record:read, vehicle-document:read, vehicle-expense-record:read, vehicle-fuel-record:read, vehicle-incharge:read, vehicle-service-record:read, vehicle-trip-record:read, vehicle:read

## manager  (567 permissions)

- **academic**: academic-department:manage, batch-incharge:create, batch-incharge:delete, batch-incharge:edit, batch-incharge:export, batch-incharge:read, batch:create, batch:delete, batch:edit, batch:export, batch:read, book-list:manage, certificate-template:create, certificate-template:delete, certificate-template:edit, certificate-template:export, certificate-template:read, certificate:create, certificate:delete, certificate:edit, certificate:export, certificate:read, class-timing:create, class-timing:delete, class-timing:edit, class-timing:export, class-timing:read, course-incharge:create, course-incharge:delete, course-incharge:edit, course-incharge:export, course-incharge:read, course:create, course:delete, course:edit, course:export, course:read, division-incharge:create, division-incharge:delete, division-incharge:edit, division-incharge:export, division-incharge:read, division:create, division:delete, division:edit, division:export, division:read, id-card:manage, period:change, period:config, period:create, period:delete, period:edit, period:export, period:read, program:manage, session:manage, subject-incharge:create, subject-incharge:delete, subject-incharge:edit, subject-incharge:export, subject-incharge:read, subject:create, subject:delete, subject:edit, subject:export, subject:read, timetable:allocate, timetable:create, timetable:delete, timetable:edit, timetable:export, timetable:read
- **activity**: activity:config, trip-participant:manage, trip:manage, trip:read
- **approval**: approval-request:action, approval-request:create, approval-request:delete, approval-request:edit, approval-request:export, approval-request:read, approval-type:manage, approval:report
- **asset**: asset:config, building:manage
- **calendar**: calendar:config, celebration:export, celebration:read, event:admin-access, event:create, event:delete, event:edit, event:export, event:read, holiday:create, holiday:delete, holiday:edit, holiday:export, holiday:read
- **communication**: announcement:admin-access, announcement:create, announcement:delete, announcement:edit, announcement:export, announcement:read, announcement:view-log, communication:config
- **contact**: contact:config, contact:create, contact:delete, contact:edit, contact:export, contact:read
- **dashboard**: dashboard:stat
- **discipline**: discipline:config, incident:manage
- **employee**: attendance:admin-access, attendance:config, attendance:export, attendance:mark, attendance:read, department:create, department:delete, department:edit, department:export, department:read, designation:create, designation:delete, designation:edit, designation:export, designation:read, designation:subordinate-access, employee:config, employee:create, employee:dialogue, employee:edit, employee:edit-request-action, employee:export, employee:read, employee:self-service, employee:self-service-action, employment-record:manage, leave-allocation:admin-access, leave-allocation:create, leave-allocation:edit, leave-allocation:export, leave-allocation:read, leave-request:action, leave-request:admin-access, leave-request:create, leave-request:edit, leave-request:export, leave-request:read, leave:config, payroll:admin-access, payroll:config, payroll:create, payroll:edit, payroll:export, payroll:process, payroll:read, salary-structure:create, salary-structure:edit, salary-structure:export, salary-structure:read, salary-template:create, salary-template:edit, salary-template:export, salary-template:read, timesheet:create, timesheet:delete, timesheet:edit, timesheet:export, timesheet:import, timesheet:read, timesheet:sync, work-shift:assign, work-shift:create, work-shift:delete, work-shift:edit, work-shift:export, work-shift:read
- **exam**: exam-admit-card:access, exam-assessment:manage, exam-competency:manage, exam-grade:manage, exam-marksheet:access, exam-marksheet:process, exam-observation:manage, exam-schedule:create, exam-schedule:delete, exam-schedule:edit, exam-schedule:export, exam-schedule:read, exam-term:manage, exam:config, exam:manage, exam:marks-record, exam:report, online-exam:create, online-exam:delete, online-exam:edit, online-exam:export, online-exam:read
- **finance**: fee-component:manage, fee-concession:create, fee-concession:delete, fee-concession:edit, fee-concession:export, fee-concession:read, fee-group:create, fee-group:delete, fee-group:edit, fee-group:export, fee-group:read, fee-head:create, fee-head:delete, fee-head:edit, fee-head:export, fee-head:read, fee-payment:import, fee-structure:allocate, fee-structure:create, fee-structure:delete, fee-structure:edit, fee-structure:export, fee-structure:read, finance:config, finance:export, finance:report, ledger-type:create, ledger-type:delete, ledger-type:edit, ledger-type:export, ledger-type:read, ledger:create, ledger:delete, ledger:edit, ledger:export, ledger:read, receipt:cancel, receipt:create, receipt:delete, receipt:edit, receipt:export, receipt:read, transaction:cancel, transaction:config, transaction:create, transaction:delete, transaction:edit, transaction:export, transaction:manage-clearance, transaction:read
- **form**: form-submission:manage, form:config, form:create, form:delete, form:edit, form:export, form:read, form:submit
- **gallery**: gallery:config, gallery:create, gallery:delete, gallery:edit, gallery:export, gallery:read
- **general**: access:reports, login:action, password:update, post:comment, post:config, post:create, post:delete, post:read
- **guardian**: guardian:config, guardian:create, guardian:delete, guardian:edit, guardian:export, guardian:read
- **helpdesk**: faq:create, faq:read, ticket:action, ticket:create, ticket:edit, ticket:read
- **hostel**: hostel-incharge:create, hostel-incharge:delete, hostel-incharge:edit, hostel-incharge:export, hostel-incharge:read, hostel-room-allocation:create, hostel-room-allocation:delete, hostel-room-allocation:edit, hostel-room-allocation:export, hostel-room-allocation:read, hostel:manage
- **inventory**: inventory:admin-access, inventory:config, inventory:report, stock-adjustment:create, stock-adjustment:delete, stock-adjustment:edit, stock-adjustment:export, stock-adjustment:read, stock-category:create, stock-category:delete, stock-category:edit, stock-category:export, stock-category:read, stock-item:create, stock-item:delete, stock-item:edit, stock-item:export, stock-item:read, stock-purchase:create, stock-purchase:delete, stock-purchase:edit, stock-purchase:export, stock-purchase:read, stock-requisition:create, stock-requisition:delete, stock-requisition:edit, stock-requisition:export, stock-requisition:read, stock-return:create, stock-return:delete, stock-return:edit, stock-return:export, stock-return:read, stock-transfer:create, stock-transfer:delete, stock-transfer:edit, stock-transfer:export, stock-transfer:read, vendor:create, vendor:delete, vendor:edit, vendor:export, vendor:read
- **library**: book-addition:create, book-addition:delete, book-addition:edit, book-addition:export, book-addition:read, book:create, book:delete, book:edit, book:export, book:issue, book:read, book:return, library:config, library:report
- **mess**: meal-log:create, meal-log:delete, meal-log:edit, meal-log:export, meal-log:read, meal:manage, menu-item:manage, mess:config
- **reception**: call-log:create, call-log:delete, call-log:edit, call-log:export, call-log:read, complaint:action, complaint:admin-access, complaint:create, complaint:delete, complaint:edit, complaint:export, complaint:read, correspondence:create, correspondence:delete, correspondence:edit, correspondence:export, correspondence:read, enquiry:action, enquiry:admin-access, enquiry:create, enquiry:delete, enquiry:edit, enquiry:export, enquiry:follow-up, enquiry:read, gate-pass:create, gate-pass:delete, gate-pass:edit, gate-pass:export, gate-pass:read, query:action, query:delete, query:read, reception:config, visitor-log:create, visitor-log:delete, visitor-log:edit, visitor-log:export, visitor-log:read
- **recruitment**: job-application:create, job-application:delete, job-application:edit, job-application:export, job-application:read, job-vacancy:create, job-vacancy:delete, job-vacancy:edit, job-vacancy:export, job-vacancy:read, recruitment:config
- **resource**: assignment:create, assignment:delete, assignment:edit, assignment:export, assignment:read, assignment:view-log, download:create, download:delete, download:edit, download:export, download:read, learning-material:create, learning-material:delete, learning-material:edit, learning-material:export, learning-material:read, learning-material:view-log, lesson-plan:create, lesson-plan:delete, lesson-plan:edit, lesson-plan:export, lesson-plan:read, online-class:create, online-class:delete, online-class:edit, online-class:export, online-class:read, resource:config, resource:report, student-diary:create, student-diary:delete, student-diary:edit, student-diary:export, student-diary:read, student-diary:view-log, syllabus:create, syllabus:delete, syllabus:edit, syllabus:export, syllabus:read
- **site**: site:manage
- **student**: alumni:read, fee:bank-transfer-action, fee:cancel-payment, fee:change-payment-date, fee:custom-concession, fee:customize-late-fee, fee:edit, fee:edit-payment, fee:flexible-installment-payment, fee:head-wise-payment, fee:manage-force-custom-fee, fee:multiple-installment-payment, fee:partial-payment, fee:payment, fee:reset, fee:set, registration:action, registration:create, registration:delete, registration:edit, registration:export, registration:fee, registration:read, registration:verify, student-record:manage, student:admin-access, student:config, student:create, student:delete, student:dialogue, student:edit, student:edit-request-action, student:export, student:leave-request, student:list-attendance, student:mark-attendance, student:promotion, student:read, student:report, student:service-allocation, student:service-request, student:service-request-action, student:transfer, student:transfer-request, student:transfer-request-action
- **task**: task:create, task:edit, task:export, task:read
- **transport**: transport-circle:create, transport-circle:delete, transport-circle:edit, transport-circle:export, transport-circle:read, transport-fee:create, transport-fee:delete, transport-fee:edit, transport-fee:export, transport-fee:read, transport-route:create, transport-route:delete, transport-route:edit, transport-route:export, transport-route:read, transport-stoppage:manage, transport:config, transport:export, transport:report, vehicle-case-record:create, vehicle-case-record:delete, vehicle-case-record:edit, vehicle-case-record:export, vehicle-case-record:read, vehicle-document:create, vehicle-document:delete, vehicle-document:edit, vehicle-document:export, vehicle-document:read, vehicle-expense-record:create, vehicle-expense-record:delete, vehicle-expense-record:edit, vehicle-expense-record:export, vehicle-expense-record:read, vehicle-fuel-record:create, vehicle-fuel-record:delete, vehicle-fuel-record:edit, vehicle-fuel-record:export, vehicle-fuel-record:read, vehicle-incharge:create, vehicle-incharge:delete, vehicle-incharge:edit, vehicle-incharge:export, vehicle-incharge:read, vehicle-service-record:create, vehicle-service-record:delete, vehicle-service-record:edit, vehicle-service-record:export, vehicle-service-record:read, vehicle-trip-record:create, vehicle-trip-record:delete, vehicle-trip-record:edit, vehicle-trip-record:export, vehicle-trip-record:read, vehicle:config, vehicle:create, vehicle:delete, vehicle:edit, vehicle:export, vehicle:read
- **utility**: todo:manage

## principal  (468 permissions)

- **academic**: academic-department:manage, batch-incharge:create, batch-incharge:delete, batch-incharge:edit, batch-incharge:export, batch-incharge:read, batch:create, batch:edit, batch:export, batch:read, book-list:manage, certificate-template:create, certificate-template:delete, certificate-template:edit, certificate-template:export, certificate-template:read, certificate:create, certificate:delete, certificate:edit, certificate:export, certificate:read, class-timing:create, class-timing:delete, class-timing:edit, class-timing:export, class-timing:read, course-incharge:create, course-incharge:delete, course-incharge:edit, course-incharge:export, course-incharge:read, course:create, course:edit, course:export, course:read, division-incharge:create, division-incharge:delete, division-incharge:edit, division-incharge:export, division-incharge:read, division:create, division:edit, division:export, division:read, id-card:manage, period:change, period:create, period:edit, period:export, period:read, program:manage, session:manage, subject-incharge:create, subject-incharge:delete, subject-incharge:edit, subject-incharge:export, subject-incharge:read, subject:create, subject:edit, subject:export, subject:read, timetable:allocate, timetable:create, timetable:delete, timetable:edit, timetable:export, timetable:read
- **activity**: trip:read
- **approval**: approval-request:action, approval-request:create, approval-request:delete, approval-request:edit, approval-request:export, approval-request:read, approval-type:manage, approval:report
- **calendar**: celebration:export, celebration:read, event:admin-access, event:create, event:delete, event:edit, event:export, event:read, holiday:create, holiday:delete, holiday:edit, holiday:export, holiday:read
- **communication**: announcement:admin-access, announcement:create, announcement:delete, announcement:edit, announcement:export, announcement:read, announcement:view-log
- **contact**: contact:create, contact:delete, contact:edit, contact:export, contact:read
- **dashboard**: dashboard:stat
- **discipline**: incident:manage
- **employee**: attendance:admin-access, attendance:mark, attendance:read, department:read, designation:read, designation:subordinate-access, employee:dialogue, employee:edit-request-action, employee:read, employee:self-service, leave-allocation:admin-access, leave-allocation:create, leave-allocation:read, leave-request:action, leave-request:admin-access, leave-request:create, leave-request:read, payroll:admin-access, payroll:read, timesheet:read, work-shift:assign, work-shift:create, work-shift:edit, work-shift:read
- **exam**: exam-admit-card:access, exam-assessment:manage, exam-competency:manage, exam-grade:manage, exam-marksheet:access, exam-marksheet:process, exam-observation:manage, exam-schedule:create, exam-schedule:edit, exam-schedule:export, exam-schedule:read, exam-term:manage, exam:manage, exam:marks-record, online-exam:create, online-exam:delete, online-exam:edit, online-exam:export, online-exam:read
- **finance**: fee-concession:create, fee-concession:delete, fee-concession:edit, fee-concession:export, fee-concession:read, fee-group:create, fee-group:delete, fee-group:edit, fee-group:export, fee-group:read, fee-head:create, fee-head:delete, fee-head:edit, fee-head:export, fee-head:read, fee-payment:import, fee-structure:allocate, fee-structure:create, fee-structure:delete, fee-structure:edit, fee-structure:export, fee-structure:read, ledger-type:create, ledger-type:delete, ledger-type:edit, ledger-type:export, ledger-type:read, ledger:create, ledger:delete, ledger:edit, ledger:export, ledger:read, receipt:cancel, receipt:create, receipt:edit, receipt:export, receipt:read, transaction:cancel, transaction:config, transaction:create, transaction:edit, transaction:export, transaction:manage-clearance, transaction:read
- **form**: form-submission:manage, form:create, form:delete, form:edit, form:export, form:read, form:submit
- **gallery**: gallery:create, gallery:delete, gallery:edit, gallery:export, gallery:read
- **general**: login:action, password:update, post:comment, post:create, post:read
- **guardian**: guardian:create, guardian:delete, guardian:edit, guardian:export, guardian:read
- **helpdesk**: faq:read, ticket:action, ticket:create, ticket:edit, ticket:read
- **hostel**: hostel-incharge:create, hostel-incharge:delete, hostel-incharge:edit, hostel-incharge:export, hostel-incharge:read, hostel-room-allocation:create, hostel-room-allocation:delete, hostel-room-allocation:edit, hostel-room-allocation:export, hostel-room-allocation:read
- **inventory**: inventory:admin-access, inventory:report, stock-adjustment:create, stock-adjustment:edit, stock-adjustment:export, stock-adjustment:read, stock-category:create, stock-category:edit, stock-category:export, stock-category:read, stock-item:create, stock-item:edit, stock-item:export, stock-item:read, stock-purchase:create, stock-purchase:edit, stock-purchase:export, stock-purchase:read, stock-requisition:create, stock-requisition:edit, stock-requisition:export, stock-requisition:read, stock-return:create, stock-return:edit, stock-return:export, stock-return:read, stock-transfer:create, stock-transfer:edit, stock-transfer:export, stock-transfer:read, vendor:create, vendor:edit, vendor:export, vendor:read
- **library**: book-addition:create, book-addition:delete, book-addition:edit, book-addition:export, book-addition:read, book:create, book:delete, book:edit, book:export, book:issue, book:read, book:return, library:report
- **mess**: meal-log:create, meal-log:delete, meal-log:edit, meal-log:export, meal-log:read, meal:manage, menu-item:manage
- **reception**: call-log:create, call-log:delete, call-log:edit, call-log:export, call-log:read, complaint:action, complaint:admin-access, complaint:create, complaint:delete, complaint:edit, complaint:export, complaint:read, correspondence:create, correspondence:delete, correspondence:edit, correspondence:export, correspondence:read, enquiry:action, enquiry:admin-access, enquiry:create, enquiry:delete, enquiry:edit, enquiry:export, enquiry:follow-up, enquiry:read, gate-pass:create, gate-pass:delete, gate-pass:edit, gate-pass:export, gate-pass:read, query:action, query:delete, query:read, visitor-log:create, visitor-log:delete, visitor-log:edit, visitor-log:export, visitor-log:read
- **recruitment**: job-application:create, job-application:delete, job-application:edit, job-application:export, job-application:read, job-vacancy:create, job-vacancy:delete, job-vacancy:edit, job-vacancy:export, job-vacancy:read
- **resource**: assignment:create, assignment:delete, assignment:edit, assignment:export, assignment:read, assignment:view-log, download:create, download:delete, download:edit, download:export, download:read, learning-material:create, learning-material:delete, learning-material:edit, learning-material:export, learning-material:read, learning-material:view-log, lesson-plan:create, lesson-plan:delete, lesson-plan:edit, lesson-plan:export, lesson-plan:read, online-class:create, online-class:delete, online-class:edit, online-class:export, online-class:read, student-diary:create, student-diary:delete, student-diary:edit, student-diary:export, student-diary:read, student-diary:view-log, syllabus:create, syllabus:delete, syllabus:edit, syllabus:export, syllabus:read
- **student**: alumni:read, fee:bank-transfer-action, fee:cancel-payment, fee:change-payment-date, fee:custom-concession, fee:customize-late-fee, fee:edit, fee:edit-payment, fee:flexible-installment-payment, fee:head-wise-payment, fee:manage-force-custom-fee, fee:multiple-installment-payment, fee:partial-payment, fee:payment, fee:reset, fee:set, registration:action, registration:create, registration:delete, registration:edit, registration:export, registration:fee, registration:read, registration:verify, student-record:manage, student:admin-access, student:create, student:delete, student:dialogue, student:edit, student:edit-request-action, student:export, student:leave-request, student:list-attendance, student:mark-attendance, student:promotion, student:read, student:service-allocation, student:service-request, student:service-request-action, student:transfer, student:transfer-request, student:transfer-request-action
- **transport**: transport-circle:create, transport-circle:delete, transport-circle:edit, transport-circle:export, transport-circle:read, transport-fee:create, transport-fee:delete, transport-fee:edit, transport-fee:export, transport-fee:read, transport-route:create, transport-route:delete, transport-route:edit, transport-route:export, transport-route:read, transport-stoppage:manage, vehicle-case-record:create, vehicle-case-record:delete, vehicle-case-record:edit, vehicle-case-record:export, vehicle-case-record:read, vehicle-document:create, vehicle-document:delete, vehicle-document:edit, vehicle-document:export, vehicle-document:read, vehicle-expense-record:create, vehicle-expense-record:delete, vehicle-expense-record:edit, vehicle-expense-record:export, vehicle-expense-record:read, vehicle-fuel-record:create, vehicle-fuel-record:delete, vehicle-fuel-record:edit, vehicle-fuel-record:export, vehicle-fuel-record:read, vehicle-incharge:create, vehicle-incharge:delete, vehicle-incharge:edit, vehicle-incharge:export, vehicle-incharge:read, vehicle-service-record:create, vehicle-service-record:delete, vehicle-service-record:edit, vehicle-service-record:export, vehicle-service-record:read, vehicle-trip-record:create, vehicle-trip-record:delete, vehicle-trip-record:edit, vehicle-trip-record:export, vehicle-trip-record:read, vehicle:config, vehicle:create, vehicle:delete, vehicle:edit, vehicle:export, vehicle:read
- **utility**: todo:manage

## staff  (80 permissions)

- **academic**: academic:incharge-access, batch:read, course:read, division:read, period:change, subject:read
- **activity**: trip:read
- **approval**: approval-request:action, approval-request:create, approval-request:edit, approval-request:read
- **calendar**: celebration:read, event:read
- **communication**: announcement:read, announcement:view-log
- **employee**: attendance:read, designation:subordinate-access, employee:read, employee:self-service, leave-allocation:read, leave-request:create, leave-request:read, payroll:read, timesheet:read, work-shift:read
- **exam**: exam-marksheet:access, exam-schedule:read, exam:subject-incharge-wise-marks-record, online-exam:create, online-exam:edit, online-exam:export, online-exam:read
- **form**: form:submit
- **gallery**: gallery:read
- **general**: login:action, password:update, post:comment, post:read
- **helpdesk**: faq:read, ticket:create, ticket:read
- **resource**: assignment:create, assignment:edit, assignment:export, assignment:read, assignment:view-log, book-list:read, download:create, download:edit, download:export, download:read, learning-material:create, learning-material:edit, learning-material:export, learning-material:read, learning-material:view-log, lesson-plan:create, lesson-plan:edit, lesson-plan:export, lesson-plan:read, online-class:create, online-class:edit, online-class:export, online-class:read, student-diary:create, student-diary:edit, student-diary:read, student-diary:view-log, syllabus:create, syllabus:edit, syllabus:export, syllabus:read
- **student**: student-record:manage, student:dialogue, student:incharge-access, student:incharge-wise-mark-attendance, student:leave-request, student:list-attendance, student:read
- **utility**: todo:manage

## accountant  (73 permissions)

- **academic**: batch:read, course:read, division:read, period:change
- **approval**: approval-request:action, approval-request:create, approval-request:read
- **calendar**: event:read
- **communication**: announcement:read
- **dashboard**: dashboard:stat
- **employee**: attendance:read, employee:read, leave-allocation:read, leave-request:create, leave-request:read, payroll:read, timesheet:read, work-shift:read
- **finance**: fee-concession:create, fee-concession:edit, fee-concession:export, fee-concession:read, fee-group:create, fee-group:edit, fee-group:export, fee-group:read, fee-head:create, fee-head:edit, fee-head:export, fee-head:read, fee-payment:import, fee-structure:allocate, fee-structure:create, fee-structure:edit, fee-structure:export, fee-structure:read, ledger-type:create, ledger-type:edit, ledger-type:export, ledger-type:read, ledger:create, ledger:edit, ledger:export, ledger:read, receipt:cancel, receipt:create, receipt:edit, receipt:export, receipt:read, transaction:cancel, transaction:create, transaction:edit, transaction:export, transaction:manage-clearance, transaction:read
- **general**: login:action, password:update, post:comment, post:read
- **student**: fee:bank-transfer-action, fee:cancel-payment, fee:change-payment-date, fee:customize-late-fee, fee:edit, fee:edit-payment, fee:head-wise-payment, fee:partial-payment, fee:payment, fee:reset, fee:set, registration:fee, student:read
- **utility**: todo:manage

## librarian  (35 permissions)

- **academic**: batch:read, course:read, division:read, period:change
- **approval**: approval-request:action, approval-request:create, approval-request:read
- **calendar**: event:read
- **communication**: announcement:read
- **employee**: attendance:read, employee:read, employee:summary, leave-allocation:read, leave-request:create, leave-request:read, payroll:read, timesheet:read, work-shift:read
- **general**: login:action, password:update, post:comment, post:read
- **library**: book-addition:create, book-addition:edit, book-addition:export, book-addition:read, book:create, book:edit, book:export, book:issue, book:read, book:return
- **student**: student:read, student:summary
- **utility**: todo:manage

## exam-incharge  (38 permissions)

- **academic**: batch:read, course:read, division:read, period:change
- **approval**: approval-request:action, approval-request:create, approval-request:read
- **calendar**: event:read
- **communication**: announcement:read
- **employee**: attendance:read, employee:read, employee:summary, leave-allocation:read, leave-request:create, leave-request:read, payroll:read, timesheet:read, work-shift:read
- **exam**: exam-admit-card:access, exam-marksheet:access, exam-marksheet:process, exam-schedule:create, exam-schedule:edit, exam-schedule:export, exam-schedule:read, exam:marks-record, online-exam:create, online-exam:delete, online-exam:edit, online-exam:export, online-exam:read
- **general**: login:action, password:update, post:comment, post:read
- **student**: student:read, student:summary
- **utility**: todo:manage

## transport-incharge  (71 permissions)

- **academic**: batch:read, course:read, division:read, period:change
- **approval**: approval-request:action, approval-request:create, approval-request:read
- **calendar**: event:read
- **communication**: announcement:read
- **employee**: attendance:read, employee:read, employee:summary, leave-allocation:read, leave-request:create, leave-request:read, payroll:read, timesheet:read, work-shift:read
- **general**: login:action, password:update, post:comment, post:read
- **student**: student:read, student:summary
- **transport**: transport-circle:create, transport-circle:edit, transport-circle:export, transport-circle:read, transport-fee:create, transport-fee:edit, transport-fee:export, transport-fee:read, transport-route:create, transport-route:edit, transport-route:export, transport-route:read, transport-stoppage:manage, vehicle-case-record:create, vehicle-case-record:edit, vehicle-case-record:export, vehicle-case-record:read, vehicle-document:create, vehicle-document:edit, vehicle-document:export, vehicle-document:read, vehicle-expense-record:create, vehicle-expense-record:edit, vehicle-expense-record:export, vehicle-expense-record:read, vehicle-fuel-record:create, vehicle-fuel-record:edit, vehicle-fuel-record:export, vehicle-fuel-record:read, vehicle-incharge:create, vehicle-incharge:edit, vehicle-incharge:export, vehicle-incharge:read, vehicle-service-record:create, vehicle-service-record:edit, vehicle-service-record:export, vehicle-service-record:read, vehicle-trip-record:create, vehicle-trip-record:edit, vehicle-trip-record:export, vehicle-trip-record:read, vehicle:config, vehicle:create, vehicle:edit, vehicle:export, vehicle:read
- **utility**: todo:manage

## inventory-incharge  (58 permissions)

- **academic**: batch:read, course:read, division:read, period:change
- **approval**: approval-request:action, approval-request:create, approval-request:read
- **calendar**: event:read
- **communication**: announcement:read
- **employee**: attendance:read, employee:read, employee:summary, leave-allocation:read, leave-request:create, leave-request:read, payroll:read, timesheet:read, work-shift:read
- **general**: login:action, password:update, post:comment, post:read
- **inventory**: inventory:report, stock-adjustment:create, stock-adjustment:edit, stock-adjustment:export, stock-adjustment:read, stock-category:create, stock-category:edit, stock-category:export, stock-category:read, stock-item:create, stock-item:edit, stock-item:export, stock-item:read, stock-purchase:create, stock-purchase:edit, stock-purchase:export, stock-purchase:read, stock-requisition:create, stock-requisition:edit, stock-requisition:export, stock-requisition:read, stock-return:create, stock-return:edit, stock-return:export, stock-return:read, stock-transfer:create, stock-transfer:edit, stock-transfer:export, stock-transfer:read, vendor:create, vendor:edit, vendor:export, vendor:read
- **student**: student:read, student:summary
- **utility**: todo:manage

## mess-incharge  (16 permissions)

- **approval**: approval-request:action, approval-request:create, approval-request:read
- **employee**: employee:summary
- **general**: login:action, password:update, post:comment, post:read
- **mess**: meal-log:create, meal-log:edit, meal-log:export, meal-log:read, meal:manage, menu-item:manage
- **student**: student:summary
- **utility**: todo:manage

## hostel-incharge  (15 permissions)

- **approval**: approval-request:action, approval-request:create, approval-request:read
- **employee**: employee:summary
- **general**: login:action, password:update, post:comment, post:read
- **hostel**: hostel-incharge:read, hostel-room-allocation:create, hostel-room-allocation:edit, hostel-room-allocation:read, hostel:manage
- **student**: student:summary
- **utility**: todo:manage

## attendance-assistant  (2 permissions)

- **general**: login:action
- **utility**: todo:manage

## receptionist  (68 permissions)

- **academic**: batch:read, course:read, division:read, period:change
- **approval**: approval-request:action, approval-request:create, approval-request:read
- **calendar**: event:read
- **communication**: announcement:create, announcement:read
- **contact**: contact:create, contact:edit, contact:export, contact:read
- **employee**: attendance:read, employee:read, employee:summary, leave-allocation:read, leave-request:create, leave-request:read, payroll:read, timesheet:read, work-shift:read
- **general**: login:action, password:update, post:comment, post:read
- **guardian**: guardian:create, guardian:edit, guardian:export, guardian:read
- **reception**: call-log:create, call-log:edit, call-log:export, call-log:read, complaint:create, complaint:edit, complaint:export, complaint:read, correspondence:create, correspondence:edit, correspondence:export, correspondence:read, enquiry:create, enquiry:edit, enquiry:export, enquiry:follow-up, enquiry:read, gate-pass:create, gate-pass:edit, gate-pass:export, gate-pass:read, query:action, query:read, visitor-log:create, visitor-log:edit, visitor-log:export, visitor-log:read
- **student**: fee:cancel-payment, fee:payment, registration:action, registration:create, registration:edit, registration:read, student:create, student:read, student:summary
- **utility**: todo:manage

## student  (29 permissions)

- **activity**: trip:read
- **calendar**: event:read
- **communication**: announcement:read
- **exam**: exam-marksheet:access, exam-schedule:read, online-exam:read
- **form**: form:submit
- **gallery**: gallery:read
- **general**: login:action, password:update, post:comment, post:read
- **reception**: complaint:create, complaint:edit, complaint:read
- **resource**: assignment:read, book-list:read, learning-material:read, online-class:read, student-diary:read
- **student**: fee:payment, student:dialogue, student:leave-request, student:list-attendance, student:read, student:self-access, student:service-request, student:transfer-request
- **utility**: todo:manage

## guardian  (29 permissions)

- **activity**: trip:read
- **calendar**: event:read
- **communication**: announcement:read
- **exam**: exam-marksheet:access, exam-schedule:read, online-exam:read
- **form**: form:submit
- **gallery**: gallery:read
- **general**: login:action, password:update, post:comment, post:read
- **reception**: complaint:create, complaint:edit, complaint:read
- **resource**: assignment:read, book-list:read, learning-material:read, online-class:read, student-diary:read
- **student**: fee:payment, student:dialogue, student:leave-request, student:list-attendance, student:read, student:self-access, student:service-request, student:transfer-request
- **utility**: todo:manage

## user  (0 permissions)


