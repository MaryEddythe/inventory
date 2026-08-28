# Attendance Monitoring Feature

## Goal

Add an `Attendance` section in the HR sidebar so HR can monitor employee attendance behavior, late arrivals, absences, and leave activity in one place.

This feature should help HR:

- see employees who are late on a given day
- track how many lates each employee has for the current month
- notify employees when they reach their 7th late in a month
- flag employees who reach 10 lates in a month for memo action
- monitor absences, especially for regular employees
- remind absent employees to explain their absence or file a leave application

## Sidebar

Add a new sidebar item:

- Label: `Attendance`
- Suggested route: `attendance.index`
- Suggested icon: attendance or clock icon

The `Attendance` menu can later contain sub-items such as:

- Daily Attendance
- Monthly Late Summary
- Absence Monitoring
- Leave Follow-up

## Core Rules

### Late tracking

- Each employee late entry should be recorded with the date and time.
- HR should be able to view the total number of lates per month per employee.
- When an employee reaches `7 lates` in the current month, the system should send a notification warning them about the habit.
- When an employee reaches `10 lates` in the current month, the system should mark them for memo or HR review.

### Absence monitoring

- HR should be able to see employees who are absent on a given day.
- If an employee is absent, HR should be able to send a notification asking whether the absence is valid or if the employee should file a leave application.
- The system should help HR distinguish between:
  - approved leave
  - filed leave pending approval
  - unfiled absence

## Suggested HR View

The Attendance page should show:

- employee name
- department or division
- attendance status for the day
- late count for the month
- absence count for the month
- leave application status
- warning status
- memo flag status

## Suggested Notifications

### 7th late notification

Send a warning message to the employee:

- tell them they have reached 7 lates this month
- remind them to improve attendance
- inform HR that the employee has been warned

### Absence follow-up notification

Send a message to the employee when they are absent:

- ask them to confirm if the absence is valid
- remind them to file a leave application if needed

### Memo flag

When the employee reaches 10 lates in a month:

- flag the employee for memo
- make the record visible to HR for action

## Data Needed

The feature will likely need:

- employee master data
- daily attendance records
- late count per month
- absence count per month
- leave application records
- notification history

## Notes

- This should work nicely with the existing leave application system.
- The attendance page can start simple and become more detailed later.
- If biometric attendance data becomes available, it can feed this module automatically.

