<?php

return [
    'button' => 'Help',
    'close' => 'Close',
    'default' => [
        'title' => 'How to use this page',
        'intro' => 'Use the sidebar to move between sections. Search and filters (when available) help you find records faster. Ask an admin if you need extra permissions.',
        'steps' => [
            'Read the page title to confirm you are in the right place.',
            'Use search or filters if the list is long.',
            'Open a row or button to view, edit, or add data.',
            'Save your changes before leaving the page.',
        ],
    ],
    'entries' => [
        'CurriculaCategory.create' => [
            'title' => 'Add curriculum category',
            'intro' => 'Create a new category name for organizing lectures.',
            'steps' => [
                'Enter the category name in the field.',
                'Press Add category to save.',
                'Confirm the new row appears in the categories list.',
            ],
        ],
        'CurriculaCategory.edit' => [
            'title' => 'Edit curriculum category',
            'intro' => 'Rename an existing curriculum category.',
            'steps' => [
                'Update the category name.',
                'Press Edit to save.',
                'Return to the list and verify the new name.',
            ],
        ],
        'CurriculaCategory.index' => [
            'title' => 'Curriculum categories',
            'intro' => 'Manage the category sections used when classifying lectures.',
            'steps' => [
                'Browse category IDs and names in the table.',
                'Add a new category when you need a new section.',
                'Edit a category name to keep labels clear.',
                'Delete a category only if it is no longer used.',
            ],
        ],
        'admin.custody_requests.index' => [
            'title' => 'Admin: custody requests',
            'intro' => 'Review, approve, or reject users’ custody requests. Summary counts and status filters are at the top.',
            'steps' => [
                'Check the totals for pending, approved, and rejected.',
                'Filter by status (All / Pending / Approved / Rejected).',
                'Open Details on a request to approve with partial quantities or reject with notes.',
            ],
        ],
        'admin.custody_requests.show' => [
            'title' => 'Admin: review custody request',
            'intro' => 'Approve with per-item quantities (partial approval allowed) or reject the request.',
            'steps' => [
                'Read the requester’s note and the items list.',
                'For each item set approved quantity (max = requested) and an optional item note.',
                'Add an optional general admin note, then press Approve.',
                'To refuse the request, use Reject with an optional rejection reason.',
            ],
        ],
        'admin.passwords' => [
            'title' => 'Admin passwords',
            'intro' => 'Super-admin list to find members and open password reset for their login.',
            'steps' => [
                'Search by name, Shamandora code, phone, or person ID.',
                'Confirm you have the correct person before changing anything.',
                'Open Edit password for that member.',
                'Share the new password only through a secure channel.',
            ],
        ],
        'admin.passwords.edit' => [
            'title' => 'Reset member password',
            'intro' => 'Set a new system password for the selected member.',
            'steps' => [
                'Confirm the member name shown in the form title.',
                'Enter a strong new password.',
                'Submit to save the change.',
                'Tell the member to sign in and change it again if your policy requires that.',
            ],
        ],
        'admin.place_bookings.index' => [
            'title' => 'Admin: place booking requests',
            'intro' => 'Review and approve/reject users’ place bookings. Summary counts and status filters are available.',
            'steps' => [
                'Check totals for pending, approved, and rejected.',
                'Filter by status to focus the queue.',
                'Open a request to approve (optionally changing place/time) or reject with notes.',
            ],
        ],
        'admin.place_bookings.show' => [
            'title' => 'Admin: review place booking',
            'intro' => 'Approve as requested or change approved place/date/time, or reject the booking.',
            'steps' => [
                'Review requester, sector, location, place, and user note.',
                'In Approve, set approved place, date, and From/To (defaults match the request).',
                'Add an optional admin note and press Approve.',
                'Use the reject section (when pending) if the request should be refused.',
            ],
        ],
        'app-version-settings.edit' => [
            'title' => 'App version settings',
            'intro' => 'Control what mobile apps receive from the version-check API for Android and iOS, plus maintenance and update dialog text.',
            'steps' => [
                'Set latest and minimum versions and store URLs for Android and iOS.',
                'Choose whether force update is required for each platform.',
                'Enable or disable maintenance mode and write the maintenance message.',
                'Edit the update dialog title, message, and button, then save.',
            ],
        ],
        'attendance.manage' => [
            'title' => 'Record attendance',
            'intro' => 'Mark present, absent, or excused for members of a selected season event.',
            'steps' => [
                'Choose the season and event, then load the attendance list.',
                'Search by name, phone, sector, or stage if the list is long.',
                'Set each person’s status, or use Mark all present/absent/excused.',
                'Write an excuse when status is excused, then press Save attendance.',
                'Use Send QR codes via WhatsApp to share personal QR codes before the event, or open Scan attendance at the door.',
            ],
        ],
        'attendance.scan' => [
            'title' => 'Scan attendance',
            'intro' => 'Take attendance by scanning QR codes. Reservation events support person, guest, and family codes with Present / Absent / Outside.',
            'steps' => [
                'Choose the season and event you are covering.',
                'For reservation events, QR codes are sent after first payment (SHAM / GUEST / FAM).',
                'Start the camera and scan a QR, or type the code manually.',
                'Check the card, then set Present, Absent, or Outside (you can change it later).',
            ],
        ],
        'attendance.live' => [
            'title' => 'Live attendance',
            'intro' => 'Live counters and feed for reservation event attendance (SuperAdmin, Secretary, Finance roles).',
            'steps' => [
                'Choose the season and a reservation event.',
                'Watch present / absent / outside / not scanned counts update as scanners mark people.',
                'Use the recent activity list to see who was marked and when.',
                'Download CSV for ID, name, phone, sector, status, and last update.',
                'Polling keeps the page live even if websockets are unavailable.',
            ],
        ],
        'audit-logs.index' => [
            'title' => 'Audit log',
            'intro' => 'Read-only history of important write actions (POST/PUT/PATCH/DELETE) for security tracing.',
            'steps' => [
                'Filter by person ID, HTTP method, free-text search, or date range.',
                'Apply filters, or reset to clear them.',
                'Open Data under an action to inspect the request payload when needed.',
                'Use this page for investigation only — it does not edit business data.',
            ],
        ],
        'betaka.create' => [
            'title' => 'Add progress card permit',
            'intro' => 'Create a new Egazet Betakat Taqaddom name in control data.',
            'steps' => [
                'Type the permit name in the field.',
                'Press Add to save.',
                'Confirm it appears in the permits list.',
            ],
        ],
        'betaka.edit' => [
            'title' => 'Edit progress card permit',
            'intro' => 'Rename an existing Egazet Betakat Taqaddom entry.',
            'steps' => [
                'Check the ID shown for the record.',
                'Update the permit name.',
                'Press Edit to save the change.',
            ],
        ],
        'betaka.index' => [
            'title' => 'Progress card permits',
            'intro' => 'Admin lookup values for Egazet Betakat Taqaddom (progress card permits).',
            'steps' => [
                'Review the list of permit names and IDs.',
                'Add a new permit when a new type is needed.',
                'Edit an existing name to keep data consistent.',
                'Delete a permit only if it is unused and you are sure.',
            ],
        ],
        'blood.create' => [
            'title' => 'Add blood type',
            'intro' => 'Create a new blood type label.',
            'steps' => [
                'Enter the blood type name (for example A+).',
                'Match standard medical notation used elsewhere.',
                'Press Add to save.',
                'Confirm it appears on the blood types list.',
            ],
        ],
        'blood.edit' => [
            'title' => 'Edit blood type',
            'intro' => 'Update an existing blood type label.',
            'steps' => [
                'Correct the blood type name.',
                'Double-check against the intended medical value.',
                'Press Save to apply.',
                'Members linked to this ID will show the new text.',
            ],
        ],
        'blood.index' => [
            'title' => 'Blood types',
            'intro' => 'Admin lookup of blood type values used on member profiles.',
            'steps' => [
                'Browse or search blood types in the table.',
                'Add a type only if a valid medical label is missing.',
                'Edit a label to fix spelling.',
                'Do not delete types that members already use.',
            ],
        ],
        'curricula.create' => [
            'title' => 'Upload new lecture',
            'intro' => 'Add a curricula lecture with category, stage, and file upload.',
            'steps' => [
                'Enter the lecture name.',
                'Choose the category and stage (marhala).',
                'Select the lecture file to upload.',
                'Press Upload and save, or cancel to return to the list.',
            ],
        ],
        'curricula.edit' => [
            'title' => 'Edit lecture',
            'intro' => 'Update a lecture’s name, category, and stage, then save.',
            'steps' => [
                'Change the lecture name if needed.',
                'Update the category and stage selections.',
                'Press Update and save.',
                'Return to the curricula list to confirm.',
            ],
        ],
        'curricula.index' => [
            'title' => 'Manage curricula',
            'intro' => 'Browse lecture materials by stage and category; download or maintain entries.',
            'steps' => [
                'Find a lecture by name, stage, category, or servant name.',
                'Use Download to get the lecture file.',
                'Add a new lecture when you need to upload material.',
                'Edit or delete a lecture if your role allows it.',
            ],
        ],
        'custody_requests.create' => [
            'title' => 'New custody request',
            'intro' => 'Request custody items for a date range: pick dates, search inventory, set quantities, then submit.',
            'steps' => [
                'Choose From/To dates (or Same day to lock To to From).',
                'Optionally select sector and event type.',
                'Search items (at least 2 letters), add them, and set required quantities.',
                'Add an optional note for admin, then press Send request (status will be Pending review).',
            ],
        ],
        'custody_requests.edit' => [
            'title' => 'Edit custody request',
            'intro' => 'Change a custody request only while it is still pending review.',
            'steps' => [
                'Update the date range and optional sector/event type.',
                'Search to add items, change quantities, or remove items from the list.',
                'Update your note if needed.',
                'Press Save changes, or Back to return to details.',
            ],
        ],
        'custody_requests.my' => [
            'title' => 'My custody requests',
            'intro' => 'Track the status of your custody (equipment) requests.',
            'steps' => [
                'Review each request’s dates, sector, event type, status, and reviewer.',
                'Press New custody request to start a new request.',
                'Open View for full item details.',
                'While status is Pending review, you can Edit or Delete the request.',
            ],
        ],
        'custody_requests.show' => [
            'title' => 'Custody request details',
            'intro' => 'See status, reviewer notes, and requested vs approved quantities for each item.',
            'steps' => [
                'Check status, sector, event type, and reviewer badges.',
                'Read your note and any admin note.',
                'In the items table, compare requested vs approved quantities (yellow rows mean quantity was reduced).',
                'If still pending, use Edit or Delete; otherwise use Back to the list.',
            ],
        ],
        'district.create' => [
            'title' => 'Add residential district',
            'intro' => 'Create a new residential district name.',
            'steps' => [
                'Enter the district name.',
                'Use the common local spelling people expect on forms.',
                'Press Add district to save.',
                'Check the districts list after saving.',
            ],
        ],
        'district.edit' => [
            'title' => 'Edit residential district',
            'intro' => 'Update an existing residential district name.',
            'steps' => [
                'Edit the district name.',
                'Confirm the correct district before saving.',
                'Press Save to apply.',
                'Linked addresses keep the same district ID.',
            ],
        ],
        'district.index' => [
            'title' => 'Residential districts',
            'intro' => 'Lookup of residential district names used in address and registration data.',
            'steps' => [
                'Browse or search districts by name.',
                'Add a district when a new area name is required on forms.',
                'Edit a district to correct its spelling.',
                'Delete only districts that are unused in member records.',
            ],
        ],
        'entry-questions.create' => [
            'title' => 'Add entry question',
            'intro' => 'Create a new form question for a scout sector, with optional multiple-choice answers.',
            'steps' => [
                'Select the scout sector and question type.',
                'Write the question text and set whether it is required.',
                'For multiple choice, set the number of choices (max 6) and fill each option.',
                'Press save to add the question to the form.',
            ],
        ],
        'entry-questions.edit' => [
            'title' => 'Edit entry question',
            'intro' => 'Update sector, question text, visibility, required flag, and choices.',
            'steps' => [
                'Change sector or question text as needed (question type is shown read-only).',
                'Toggle hide question or required as appropriate.',
                'For multiple choice, add/remove choices; each visible choice needs a value.',
                'Press Save changes.',
            ],
        ],
        'entry-questions.index' => [
            'title' => 'Manage entry questions',
            'intro' => 'Configure questions shown on the data-entry form by sector and type.',
            'steps' => [
                'Browse questions by type, sector, text, and choice options.',
                'Check whether a question is hidden or required.',
                'Add a new question for a sector when the form needs it.',
                'Edit or delete a question to keep the form accurate.',
            ],
        ],
        'event-type.create' => [
            'title' => 'Add event type',
            'intro' => 'Add a new occasion type name to the catalog.',
            'steps' => [
                'Type the event type name in the form field.',
                'Press the submit button to save the new type.',
                'Return to the list to confirm it appears and can be used when creating events.',
            ],
        ],
        'event-type.edit' => [
            'title' => 'Edit event type',
            'intro' => 'Rename an existing event/occasion type.',
            'steps' => [
                'Update the event type name in the field.',
                'Press the submit button to save the change.',
                'Check the list to confirm the new name is shown.',
            ],
        ],
        'event-type.index' => [
            'title' => 'Event types',
            'intro' => 'Manage the list of occasion/event type names used when creating events.',
            'steps' => [
                'Search the table to find a type by name or ID.',
                'Press the add button to create a new event type name.',
                'Use Edit to rename a type.',
                'Use Delete to remove a type that is no longer needed.',
            ],
        ],
        'event.create' => [
            'title' => 'Add event / occasion',
            'intro' => 'Create a new occasion with type, sectors, dates, and optional season link.',
            'steps' => [
                'Optionally choose a season, then select the event type and tick at least one sector.',
                'Leave the name empty to auto-generate it from type + sectors + dates, or type a custom name.',
                'For one date range, fill start/end dates. For multiple separate days, check Recurring and use + Add day.',
                'Press Enter to save. End date can match start date if you confirm the prompt.',
            ],
        ],
        'event.edit' => [
            'title' => 'Edit event / occasion',
            'intro' => 'Update an existing occasion’s season, type, name, sectors, and dates.',
            'steps' => [
                'Change the optional season link if needed.',
                'Update event type, name, and which sectors are checked.',
                'Set start and end dates (end can equal start if you confirm).',
                'Press Update to save. An empty name will be auto-generated from type, sectors, and dates.',
            ],
        ],
        'event.index' => [
            'title' => 'Occasions / events',
            'intro' => 'Manage scout occasions: type, name, dates, and linked sectors.',
            'steps' => [
                'Search or filter the list by occasion type or sector.',
                'Press Add new occasion to create a single or recurring event.',
                'Use Edit to change season link, type, name, sectors, or dates.',
                'Use Delete to remove an occasion you no longer need.',
            ],
        ],
        'eventBookingFinance.create' => [
            'title' => 'Create person booking',
            'intro' => 'Book an eligible person for the event and record their first payment (and optional shirt size).',
            'steps' => [
                'Search by name, PersonID, or mobile and select the eligible person from the results.',
                'Enter the first payment amount (date is today). Choose T-shirt size if the plan includes shirts.',
                'If they cannot pay in full, check Unable to pay the full amount and set case type, discount, and notes as needed.',
                'Press Save and print receipt (or Back to return to the bookings list).',
            ],
        ],
        'eventBookingFinance.createGuestFamily' => [
            'title' => 'Add guest / family booking',
            'intro' => 'Create a booking for a guest or family member with first payment and optional discount.',
            'steps' => [
                'Choose booking type: Guest or Families.',
                'Search and select the matching guest or family record.',
                'Fill first payment date and amount, optional T-shirt size, discount, and notes.',
                'Press Save booking (or Cancel / Back to leave without saving).',
            ],
        ],
        'eventBookingFinance.createInstallment' => [
            'title' => 'Add payment',
            'intro' => 'Record the next installment for an existing booking and print a receipt.',
            'steps' => [
                'Review the booking summary: required, paid, remaining, and current installment number.',
                'Check previous payments in the table if shown.',
                'Enter the amount (auto-filled and locked on the last installment) and optional notes.',
                'Press Save and print receipt.',
            ],
        ],
        'eventBookingFinance.editLastPayment' => [
            'title' => 'Edit last payment',
            'intro' => 'Correct the amount of the most recent payment on a booking.',
            'steps' => [
                'Confirm PaymentID and installment number shown at the top.',
                'Enter the new amount.',
                'Press Save and print receipt, or Back to cancel.',
            ],
        ],
        'eventBookingFinance.index' => [
            'title' => 'Event bookings',
            'intro' => 'Manage bookings and payments for a season event: people, guests, families, exports, and refunds.',
            'steps' => [
                'Use Quick actions to add a person booking, add guest/family booking, download today’s or full CSV, or go Back to the selector.',
                'Open Quick summary to pick a payment day and view day totals vs overall collected/refunded amounts.',
                'Search or filter the bookings list by name, code, phone, sector, size, status, or remaining amount.',
                'On a row, add a payment, edit last payment, print last receipt, refund (full or partial), or View booking details.',
            ],
        ],
        'eventBookingFinance.selector' => [
            'title' => 'Choose event for booking finance',
            'intro' => 'Pick a season and a financed event before opening booking payments.',
            'steps' => [
                'Choose a season from the first dropdown.',
                'Wait for events that already have a finance plan to load, then select one.',
                'Press Enter to open that event’s bookings list.',
            ],
        ],
        'eventServantFollowup.index' => [
            'title' => 'Follow up member bookings',
            'intro' => 'Read-only view of your members who are booked for the event and who are still on the waiting list.',
            'steps' => [
                'Confirm season, event, and dates in the header.',
                'Use search/sort on the Booked table to check required, paid, and remaining amounts.',
                'Use search/sort on the Waiting list table to see who is still waiting.',
                'There are no edit actions here—use finance or waiting-list screens if changes are needed.',
            ],
        ],
        'eventServantFollowup.selector' => [
            'title' => 'Choose event for servant follow-up',
            'intro' => 'Pick season and event to follow booked members and waiting-list members under your care.',
            'steps' => [
                'Choose a season.',
                'Choose an event that has a finance plan (list loads after season selection).',
                'Press Enter to open the follow-up page for that event.',
            ],
        ],
        'eventWaitingList.index' => [
            'title' => 'Event waiting list',
            'intro' => 'Add eligible people to the waiting list for this event and remove them when needed.',
            'steps' => [
                'Search for an eligible person by name, PersonID, or mobile and select them.',
                'Press Add to waiting list to save the person.',
                'Use search/filters on the waiting list table to find someone already added.',
                'Use Delete on a row to remove them, or Change event to pick another event.',
            ],
        ],
        'eventWaitingList.selector' => [
            'title' => 'Choose event for waiting list',
            'intro' => 'Select season and event before managing that event’s waiting list.',
            'steps' => [
                'Choose a season.',
                'Choose an event from the loaded list.',
                'Press Enter to open the waiting list for that event.',
            ],
        ],
        'faculty.create' => [
            'title' => 'Add faculty',
            'intro' => 'Create a new faculty/college name.',
            'steps' => [
                'Enter the faculty name.',
                'Use the official college name when possible.',
                'Press Add faculty to save.',
                'Verify it appears on the faculties list.',
            ],
        ],
        'faculty.edit' => [
            'title' => 'Edit faculty',
            'intro' => 'Update an existing faculty/college name.',
            'steps' => [
                'Edit the faculty name.',
                'Confirm the correct faculty ID.',
                'Press Save to apply.',
                'Members linked to this faculty keep the same ID.',
            ],
        ],
        'faculty.index' => [
            'title' => 'Faculties',
            'intro' => 'Lookup of faculty/college names used for university students in member data.',
            'steps' => [
                'Browse or search faculties by name.',
                'Add a faculty when a new college option is needed.',
                'Edit a faculty name for spelling or renaming.',
                'Keep names aligned with the universities list where possible.',
            ],
        ],
        'family-members.create' => [
            'title' => 'Add family member',
            'intro' => 'Create a family-member record. Linking to one or more scout persons is optional.',
            'steps' => [
                'Enter the family member’s personal details.',
                'Add contact information when available.',
                'Optionally link them to one or more persons in the system.',
                'Save to store the family member.',
            ],
        ],
        'family-members.edit' => [
            'title' => 'Edit family member',
            'intro' => 'Update a family member’s details or change which scout persons they are linked to.',
            'steps' => [
                'Correct personal or contact fields as needed.',
                'Add or remove linked scout persons.',
                'Review the links before saving.',
                'Save to keep family relationships accurate.',
            ],
        ],
        'family-members.index' => [
            'title' => 'Manage family members',
            'intro' => 'Browse family-member records and see which scout persons each family member is linked to.',
            'steps' => [
                'Search the family members list.',
                'Use Add family member to create a new record.',
                'Open View, Edit, or Delete on a row.',
                'Check linked persons count and names in the table.',
            ],
        ],
        'family-members.show' => [
            'title' => 'View family member',
            'intro' => 'Read a family member’s details and the scout persons linked to them.',
            'steps' => [
                'Review the family member’s personal data.',
                'Check who they are linked to in the system.',
                'Use Edit if links or details need changing.',
                'Return to the family members list when done.',
            ],
        ],
        'finance.create' => [
            'title' => 'Add finance plan',
            'intro' => 'Create a payment plan for a season event: installments, deposit rules, shirt option, and price intervals.',
            'steps' => [
                'Choose the season, then choose an event that does not already have a finance plan.',
                'Set max installments, minimum deposit, whether below-minimum deposits are allowed, and whether a T-shirt is included.',
                'Add one or more price intervals (from date, to date, price). Use Add price interval or Delete interval as needed.',
                'Press Save finance plan when the intervals and rules look correct.',
            ],
        ],
        'finance.edit' => [
            'title' => 'Edit finance plan',
            'intro' => 'Update deposit/installment rules and price intervals for an existing event finance plan.',
            'steps' => [
                'Confirm the season and event shown at the top (they cannot be changed here).',
                'Adjust max installments, minimum deposit, allow-below-minimum, and T-shirt settings.',
                'Edit existing price intervals or add/remove intervals with Add price interval / Delete interval.',
                'Press Save changes to apply the updated plan.',
            ],
        ],
        'finance.index' => [
            'title' => 'Event finance plans',
            'intro' => 'Browse finance plans linked to season events: deposits, installments, and price intervals.',
            'steps' => [
                'Use the search box to find a plan by season or event name.',
                'Review columns for season, event dates, max installments, minimum deposit, and whether the plan can still be edited.',
                'Press Add finance plan to create a plan for an event that does not have one yet.',
                'Use Edit or Delete on a row when the plan is still editable/deletable.',
            ],
        ],
        'games.create' => [
            'title' => 'Add new game',
            'intro' => 'Create a scout game with description, rules, points, and optional custody needs.',
            'steps' => [
                'Enter the game title and description (required).',
                'Fill rules, points system, age group, and objective as needed.',
                'Add required custody items and a reference link if useful.',
                'Press Add game to save.',
            ],
        ],
        'games.edit' => [
            'title' => 'Edit game',
            'intro' => 'Update an existing game’s details and save the changes.',
            'steps' => [
                'Adjust title, description, rules, or points as needed.',
                'Update age group, objective, custody needs, or reference link.',
                'Press Save changes.',
                'Return to the games list to verify the update.',
            ],
        ],
        'games.index' => [
            'title' => 'Manage games',
            'intro' => 'Browse scout games with age group and open actions to view, edit, or delete.',
            'steps' => [
                'Scan the list for game name and age group.',
                'Use View to read full game details.',
                'Add a new game if your role allows it.',
                'Edit or delete a game when you need to maintain the library.',
            ],
        ],
        'games.show' => [
            'title' => 'Game details',
            'intro' => 'Read-only view of a game’s description, rules, points, and related fields.',
            'steps' => [
                'Read the title, description, and rules.',
                'Check the points system, age group, and objective.',
                'Open the reference link if one is provided.',
                'Use the back link to return to the games list.',
            ],
        ],
        'group-person.create-khadem' => [
            'title' => 'Link khadem to group',
            'intro' => 'Assign one or more leaders/servants (khadem) to a scout group with a khadem group role.',
            'steps' => [
                'Choose the scout group.',
                'Search and select one or more khadem names/codes.',
                'Choose the person’s role in the group.',
                'Save to create the group links.',
            ],
        ],
        'group-person.create-makhdoom' => [
            'title' => 'Link makhdoom to group',
            'intro' => 'Assign served members (makhdoom) to a scout group with a non-khadem group role.',
            'steps' => [
                'Choose the scout group.',
                'Search and select one or more people to link.',
                'Choose the makhdoom role in the group.',
                'Save to add them to the group.',
            ],
        ],
        'group-person.edit' => [
            'title' => 'Edit group link',
            'intro' => 'Change an existing person-to-group assignment, such as group or role (when editable).',
            'steps' => [
                'Confirm which person you are editing.',
                'Change the group if they should move.',
                'Update the group role when the form allows it.',
                'Save so the group membership stays correct.',
            ],
        ],
        'group-person.index' => [
            'title' => 'People in groups',
            'intro' => 'See which people are linked to scout groups and what role they have in each group.',
            'steps' => [
                'Search by name, code, phone, or ID.',
                'Review each person’s group role and group details.',
                'Use Add to link a khadem (leader/servant) to a group.',
                'Open Edit or Delete to change or remove a group link.',
            ],
        ],
        'group-type.create' => [
            'title' => 'Add group type',
            'intro' => 'Define a new scout group type name for the hierarchy.',
            'steps' => [
                'Enter a clear group type name.',
                'Prefer short labels that match how leaders talk about units.',
                'Press Add to save.',
                'Then use Link groups or Team structure to create groups of this type.',
            ],
        ],
        'group-type.edit' => [
            'title' => 'Edit group type',
            'intro' => 'Rename an existing group type.',
            'steps' => [
                'Update the type name carefully.',
                'Remember all groups of this type will show the new label.',
                'Press Save to apply.',
                'Avoid renaming in a way that confuses team vs patrol meaning.',
            ],
        ],
        'group-type.index' => [
            'title' => 'Group types',
            'intro' => 'Reference list of group type labels used when creating groups (e.g. team, patrol).',
            'steps' => [
                'Browse or search the group-type table.',
                'Add a type if a new organizational level is needed.',
                'Edit a type name to keep labels consistent.',
                'Delete only unused types that are not required by existing groups.',
            ],
        ],
        'group.create' => [
            'title' => 'Add group',
            'intro' => 'Create a new group with a type and optional parent group.',
            'steps' => [
                'Choose the group type (for example team or patrol).',
                'Optionally select the larger parent group that contains it.',
                'Enter the group name.',
                'Press Add group to save the hierarchy link.',
            ],
        ],
        'group.edit' => [
            'title' => 'Edit group',
            'intro' => 'Change a group’s type, parent, or name.',
            'steps' => [
                'Update the group type if the unit’s role changed.',
                'Set parent to another group, or choose No parent group when it sits at the top.',
                'Correct the group name if needed.',
                'Press Save changes to apply.',
            ],
        ],
        'group.index' => [
            'title' => 'Link groups',
            'intro' => 'Manage scout groups and how they nest (team under sector, patrol under team, etc.).',
            'steps' => [
                'Review each group’s name, type, and parent group.',
                'Search or sort to find a specific group.',
                'Use Add new group to create a group with type and optional parent.',
                'Edit a row to rename or re-parent a group; delete only when it is safe to remove.',
            ],
        ],
        'guests.create' => [
            'title' => 'Add guest',
            'intro' => 'Create a guest record and optionally link it to a person already in the system.',
            'steps' => [
                'Enter the guest’s name and contact details.',
                'Fill national ID, date of birth, and other fields as available.',
                'Link the guest to a system person when required.',
                'Save to add the guest to the list.',
            ],
        ],
        'guests.edit' => [
            'title' => 'Edit guest',
            'intro' => 'Update an existing guest’s details or the person they are linked to.',
            'steps' => [
                'Correct name, phone, email, or national ID as needed.',
                'Update the linked person if the relationship changed.',
                'Review other guest fields before saving.',
                'Save to apply the changes.',
            ],
        ],
        'guests.index' => [
            'title' => 'Manage guests',
            'intro' => 'Browse guest records linked to scout members. Guests are not full members but may be related to activities or people.',
            'steps' => [
                'Search the guest list when needed.',
                'Use Add guest to create a new record.',
                'Open View, Edit, or Delete on a guest row.',
                'Check the linked person column to see which member the guest is tied to.',
            ],
        ],
        'guests.show' => [
            'title' => 'View guest',
            'intro' => 'Read a guest’s saved details and which scout person they are linked to.',
            'steps' => [
                'Review the guest’s personal and contact information.',
                'Check the linked person if shown.',
                'Use Edit if something needs updating.',
                'Return to the guests list when finished.',
            ],
        ],
        'home' => [
            'title' => 'Home dashboard',
            'intro' => 'Your starting page after login: quick stats for your groups, shortcuts, and a calendar of related events.',
            'steps' => [
                'Check the member count card, then open it to browse people in your scope.',
                'Use the attendance, custody, place booking, or profile cards as shortcuts.',
                'Review upcoming events on the calendar on the right.',
                'Open the sidebar when you need a section that is not on the dashboard.',
            ],
        ],
        'inventory-issue.index' => [
            'title' => 'Print custody (inventory issue)',
            'intro' => 'Build a custody PDF: choose season/event, pick inventory items and quantities, then print with signature names.',
            'steps' => [
                'Choose season, then choose the event (events load after season).',
                'Search for inventory items and add them; set quantities in the selected items table (or delete a row).',
                'Choose sector and enter Issuer and Recipient names for the signature block.',
                'Press Download / print PDF when an event and at least one item are selected.',
            ],
        ],
        'inventory.create' => [
            'title' => 'Add inventory item',
            'intro' => 'Create a new custody/inventory item with its quantity and storage details.',
            'steps' => [
                'Enter the item name (required).',
                'Set quantity and choose a measuring unit.',
                'Select category and location from the lists.',
                'Press Add item to save.',
            ],
        ],
        'inventory.edit' => [
            'title' => 'Edit inventory item',
            'intro' => 'Update an existing inventory item’s details.',
            'steps' => [
                'Change the name, quantity, unit, category, or location as needed.',
                'Keep the measuring unit consistent with how the item is counted.',
                'Press Update data to save your changes.',
            ],
        ],
        'inventory.index' => [
            'title' => 'Inventory (custody items)',
            'intro' => 'Browse and manage custody/inventory items: name, quantity, unit, category, and location.',
            'steps' => [
                'Use search or sort to find an item in the list.',
                'Press Add new item to register a new inventory item.',
                'Use Edit to update quantity, unit, category, or location.',
                'Use Delete only when the item should be removed from inventory.',
            ],
        ],
        'liveform-maxlimits.create' => [
            'title' => 'Add live-form limit',
            'intro' => 'Create a maximum enrolment capacity for a sector and stage on the live form.',
            'steps' => [
                'Select the sector (qetaa).',
                'Select the stage year (sana marhala) and enrolment year if asked.',
                'Enter the max number of accepted requests.',
                'Confirm to save the new limit.',
            ],
        ],
        'liveform-maxlimits.edit' => [
            'title' => 'Edit live-form limit',
            'intro' => 'Change the maximum enrolment number for an existing sector/stage limit.',
            'steps' => [
                'Review the sector and stage you are editing.',
                'Update the max limit value.',
                'Submit to save.',
                'Return to the list to confirm the new number appears.',
            ],
        ],
        'liveform-maxlimits.index' => [
            'title' => 'Live-form capacity limits',
            'intro' => 'Manage how many new enrolments each sector/stage can accept on the live registration form.',
            'steps' => [
                'Browse the list of sector, stage, and max limit rows.',
                'Search or filter when the table is long.',
                'Add a new limit, or edit/delete an existing row.',
                'Keep limits aligned with real capacity so waiting lists stay accurate.',
            ],
        ],
        'liveform-settings.edit' => [
            'title' => 'Enrolment form control',
            'intro' => 'Open or close the public live registration form and set the message visitors see when it is closed.',
            'steps' => [
                'Choose Open to accept new enrolment requests, or Closed to block the form.',
                'Write a clear closed message for visitors when registration is off.',
                'Optionally preview the public /liveform page in a new tab.',
                'Press Save so the change applies immediately.',
            ],
        ],
        'locations.create' => [
            'title' => 'Add location',
            'intro' => 'Create a new location/area name used by places and bookings.',
            'steps' => [
                'Enter the location name in the field.',
                'Check the spelling before saving.',
                'Press Add location to save.',
            ],
        ],
        'locations.edit' => [
            'title' => 'Edit location',
            'intro' => 'Rename an existing location/area.',
            'steps' => [
                'Update the location name in the field.',
                'Confirm it still matches how places refer to this site.',
                'Press Edit location to save.',
            ],
        ],
        'locations.index' => [
            'title' => 'Manage locations',
            'intro' => 'Browse locations (sites/areas) used when defining places and bookings.',
            'steps' => [
                'Search or sort the locations list.',
                'Press Add location to create a new location.',
                'Use Edit to rename a location.',
                'Use Delete only when the location is no longer needed.',
            ],
        ],
        'manteqa.create' => [
            'title' => 'Add residential area',
            'intro' => 'Create a new residential area (manteqa) name.',
            'steps' => [
                'Enter the area name.',
                'Prefer official or commonly used local names.',
                'Press Add residential area to save.',
                'Confirm it on the residential areas list.',
            ],
        ],
        'manteqa.edit' => [
            'title' => 'Edit residential area',
            'intro' => 'Update an existing residential area name.',
            'steps' => [
                'Correct the area name.',
                'Confirm you opened the intended area.',
                'Press Save to apply.',
                'Linked records keep the same area ID.',
            ],
        ],
        'manteqa.index' => [
            'title' => 'Residential areas',
            'intro' => 'Lookup of broader residential area (manteqa) names used with address data.',
            'steps' => [
                'Browse or search residential areas.',
                'Add an area when forms need a new manteqa option.',
                'Edit an area name to keep geography labels accurate.',
                'Coordinate with districts so area and district names stay consistent.',
            ],
        ],
        'marhala.create' => [
            'title' => 'Add academic stage',
            'intro' => 'Create a new broad academic stage name.',
            'steps' => [
                'Enter the stage name.',
                'Keep naming consistent with existing stages.',
                'Press Add academic stage to save.',
                'Return to the list to confirm it appears.',
            ],
        ],
        'marhala.edit' => [
            'title' => 'Edit academic stage',
            'intro' => 'Update an existing academic stage name.',
            'steps' => [
                'Correct the stage name as needed.',
                'Confirm you opened the right stage ID.',
                'Press Save to apply.',
                'Linked member records keep the same stage ID.',
            ],
        ],
        'marhala.index' => [
            'title' => 'Academic stages',
            'intro' => 'Admin lookup of broad academic stage names used in member education data.',
            'steps' => [
                'Browse or search stages by name or ID.',
                'Add a stage when a new education level is needed.',
                'Edit a stage to correct its display name.',
                'Delete unused stages only after checking they are not referenced elsewhere.',
            ],
        ],
        'max-limits.create' => [
            'title' => 'Add max limit',
            'intro' => 'Define a new max request limit for a sector and stage on the enrolment form.',
            'steps' => [
                'Choose the sector from the list.',
                'Choose the stage (sana marhala) and year.',
                'Type the max limit number carefully.',
                'Press confirm to insert the record.',
            ],
        ],
        'max-limits.edit' => [
            'title' => 'Edit max limit',
            'intro' => 'Update the max enrolment number for a selected sector and stage.',
            'steps' => [
                'Confirm you opened the correct sector/stage row.',
                'Change the max limit field.',
                'Submit the form to update.',
                'Verify the list shows the updated value.',
            ],
        ],
        'max-limits.index' => [
            'title' => 'Max enrolment limits',
            'intro' => 'List and manage required capacity limits used by the live enrolment form per sector and stage.',
            'steps' => [
                'Review Qetaa ID, sector name, stage, and max limit columns.',
                'Use Add to create a new capacity row.',
                'Edit a row to change its limit, or delete one that is no longer needed.',
                'Search/sort the table to find a specific sector quickly.',
            ],
        ],
        'media.create' => [
            'title' => 'Add Drive media link',
            'intro' => 'Link a season event to a Google Drive folder for the gallery.',
            'steps' => [
                'Select the season, then pick the event that appears for that season.',
                'Paste a valid Google Drive URL in the Drive link field.',
                'Press Add link to save.',
                'Return to Manage Drive links to confirm the new row.',
            ],
        ],
        'media.edit' => [
            'title' => 'Edit media Drive link',
            'intro' => 'Update the Google Drive URL for an existing media record.',
            'steps' => [
                'Review the season and event shown for this media item.',
                'Change the Drive link to the correct folder URL.',
                'Press Save changes.',
                'Use Cancel to return to the list without saving.',
            ],
        ],
        'media.index' => [
            'title' => 'Manage Drive links',
            'intro' => 'Admin list of Google Drive links tied to seasons and events.',
            'steps' => [
                'Browse the table of season, year, event, and Drive link.',
                'Use Add link to attach a new Drive folder to an event.',
                'Edit a row to change the Drive URL, or delete a link you no longer need.',
                'Use search/filters on the table when the list is long.',
            ],
        ],
        'media.pages' => [
            'title' => 'Photo gallery',
            'intro' => 'Browse Drive media folders linked to a season and event.',
            'steps' => [
                'Choose a season from the first dropdown.',
                'Choose an event; the Load media button enables when both are selected.',
                'Press Load media to show the media folders for that event.',
                'Open a folder or item to preview images or videos in the modal.',
            ],
        ],
        'medicine.create' => [
            'title' => 'Add medicine',
            'intro' => 'Register a new medicine with type, expiry, quantity, and initial storage place.',
            'steps' => [
                'Enter the medicine name and choose its type (unit label updates with the type).',
                'Set the expiry date and quantity.',
                'Choose the medicine place and optional notes.',
                'Press Add to save, or Back to return to the stock list.',
            ],
        ],
        'medicine.dispense' => [
            'title' => 'Dispense medicine',
            'intro' => 'Record giving medicine to a person from a specific place with available stock.',
            'steps' => [
                'Search and select a medicine (expired or zero-available items cannot be selected).',
                'Choose the medicine place that still has available quantity.',
                'Enter quantity (capped by available stock at that place).',
                'Search and select the person by name, code, or mobile.',
                'Add optional notes and press Record dispense.',
            ],
        ],
        'medicine.edit' => [
            'title' => 'Edit medicine',
            'intro' => 'Update medicine details and see current stock and location breakdown.',
            'steps' => [
                'Edit name, type, expiry, total quantity, and notes.',
                'Review the current stock summary and location breakdown on the card.',
                'Open Distribute stock if you need to split quantities across places.',
                'Press Update to save changes.',
            ],
        ],
        'medicine.index' => [
            'title' => 'Medicine stock',
            'intro' => 'Overview of medicines: total stock, available, locked, expiry, status, and distribution by place.',
            'steps' => [
                'Search or filter by type/status to find a medicine.',
                'Use Add medicine to create a new medicine record.',
                'Open Dispense, Dispense log, Reserve medicine, or Medicine locations from the header buttons.',
                'Use Edit, Distribute, or Delete on a row for that medicine.',
            ],
        ],
        'medicine.locations' => [
            'title' => 'Medicine locations',
            'intro' => 'Manage named places where medicines are stored (active/inactive).',
            'steps' => [
                'Type a place name (e.g. Box 4) and press Add place.',
                'Edit a name or toggle Active, then press Save.',
                'Delete a place only if it is not the fixed system place «ستوك».',
                'Use Back to return to medicine stock.',
            ],
        ],
        'medicine.locks' => [
            'title' => 'Reserve medicine',
            'intro' => 'Lock (reserve) a quantity of medicine at a place for a date range, and manage existing locks.',
            'steps' => [
                'Search and select a medicine that has available stock.',
                'Choose the lock location, quantity, start and end dates, and a reason (e.g. camp).',
                'Press Lock quantity to create the reservation.',
                'In the lock log below, use Release lock when a reservation is no longer needed.',
            ],
        ],
        'medicine.records' => [
            'title' => 'Medicine dispense log',
            'intro' => 'History of dispensed medicines: who received them, from where, by whom, and when.',
            'steps' => [
                'Browse or search the dispense records.',
                'Filter by medicine type or dispense place when needed.',
                'Open Dispense medicine to record a new dispense, or Medicine stock to return to inventory.',
            ],
        ],
        'medicine.stock' => [
            'title' => 'Distribute medicine stock',
            'intro' => 'Split one medicine’s total stock across storage places. Sums must equal total stock.',
            'steps' => [
                'Review total, available, and locked amounts at the top.',
                'Enter quantity per place (cannot go below the locked amount at that place).',
                'Ensure the sum of place quantities equals the total stock shown.',
                'Press Save distribution, or Restock to move everything back to «ستوك».',
            ],
        ],
        'person-role.create' => [
            'title' => 'Link leader to role',
            'intro' => 'Assign a system role to a khadem/leader so they get the matching permissions.',
            'steps' => [
                'Search and select the khadem/leader name.',
                'Choose the system role to assign.',
                'Confirm the selection is correct.',
                'Save to create the person–role link.',
            ],
        ],
        'person-role.edit' => [
            'title' => 'Edit leader role link',
            'intro' => 'Change the system role assigned to a leader.',
            'steps' => [
                'Confirm which leader you are editing.',
                'Select the new system role.',
                'Save the updated assignment.',
                'Return to the list to verify the change.',
            ],
        ],
        'person-role.index' => [
            'title' => 'Leader system roles',
            'intro' => 'See which leaders (khadem) are linked to system roles that control permissions and duties.',
            'steps' => [
                'Browse or search the leader–role list.',
                'Use Link leader to roles to create a new assignment.',
                'Open Edit to change a leader’s system role.',
                'Delete a link only when that role assignment should end.',
            ],
        ],
        'person-tree.index' => [
            'title' => 'Family tree',
            'intro' => 'Search for a person and display their family relationship tree based on linked family data.',
            'steps' => [
                'Search and select a person from the list.',
                'Press Show tree to load their family relationships.',
                'Explore parents, siblings, and other linked relatives on the tree.',
                'Pick another person if you need a different tree.',
            ],
        ],
        'person.ShowPersons' => [
            'title' => 'All members',
            'intro' => 'Admin view of every registered member in the system. Use it when you need the full directory beyond your usual scoped list.',
            'steps' => [
                'Search by name, code, phone, parent phone, or sector.',
                'Filter by stage or sector when the list is long.',
                'Open View or Edit on a row to work with that member.',
                'Use Add user if you need to create a new member from here.',
            ],
        ],
        'person.changeQetaa' => [
            'title' => 'Change sector',
            'intro' => 'Move a member from their current sector (qetaa) to another. Search first, then confirm the new sector.',
            'steps' => [
                'Search by name or ID and select the correct person.',
                'Confirm the person card shows the right current sector.',
                'Choose the new sector from the list.',
                'Submit the change and check the success message.',
            ],
        ],
        'person.create' => [
            'title' => 'Add new member',
            'intro' => 'Enter a new member’s personal and scout data. After saving, you may be asked to complete entry questions for their sector.',
            'steps' => [
                'Fill Part 1 personal information carefully (name, contacts, national ID).',
                'Complete the remaining sections (stage, sector, address, and related fields).',
                'Check required fields before submit.',
                'Save, then finish any entry questions if the system opens that step.',
            ],
        ],
        'person.edit' => [
            'title' => 'Edit member data',
            'intro' => 'Update an existing member’s registered information in the same style as enrolment forms.',
            'steps' => [
                'Review the current personal and scout data on the form.',
                'Change only the fields that need correction.',
                'Update photos only if new files are required.',
                'Save so the member record stays accurate.',
            ],
        ],
        'person.entry-questions' => [
            'title' => 'Complete entry questions',
            'intro' => 'Finish the sector-specific questions for a newly added member. Basic member info is shown read-only; you answer the questions below.',
            'steps' => [
                'Confirm the member code, name, and sector at the top.',
                'Answer every required question marked with *.',
                'Use the answer type shown (text or choice) for each question.',
                'Submit to mark the entry questions as completed.',
            ],
        ],
        'person.index' => [
            'title' => 'Members directory',
            'intro' => 'Browse and manage scout members you are allowed to see. Search, filter, view, edit, or add members from this list.',
            'steps' => [
                'Search by name, code, phone, parent phone, or sector.',
                'Use stage or sector column filters to narrow the list.',
                'Open View to read a member’s full record, or Edit to update it.',
                'Use Add user to register a new member.',
            ],
        ],
        'export.served-people' => [
            'title' => 'Download served people data',
            'intro' => 'Export one sector for one season as an Excel file with personal data, medical notes, sector questions, and attendance.',
            'steps' => [
                'Choose a sector you serve. SuperAdmin can choose any sector.',
                'Choose the season for the attendance sheet.',
                'Press Download Excel. Unmarked attendance stays blank.',
            ],
        ],
        'person.new-enrolments-analytics' => [
            'title' => 'New enrolment analytics',
            'intro' => 'See how many new enrolment requests each sector has, and how many of them are already approved.',
            'steps' => [
                'Scan the table by sector name.',
                'Compare total requests with approved requests.',
                'Sort or search if you need a specific sector.',
                'Use this summary before bulk migration decisions.',
            ],
        ],
        'person.new-enrolments-edit' => [
            'title' => 'Edit enrolment request',
            'intro' => 'Correct data on a pending new-enrolment request before approval or migration.',
            'steps' => [
                'Update personal or contact fields that are wrong.',
                'Adjust stage, sector, or address fields if needed.',
                'Review or correct answers to entry questions.',
                'Save so the request is ready for approval.',
            ],
        ],
        'person.new-enrolments-index' => [
            'title' => 'New enrolment requests',
            'intro' => 'Review people who applied through the registration form before they join the main system.',
            'steps' => [
                'Search or filter by stage, sector, name, phone, or national ID.',
                'Open View to read the full application and answers.',
                'Use Approve when the request is accepted, or Reject to remove it.',
                'Use Edit if data needs correction before approval.',
            ],
        ],
        'person.new-enrolments-marahel-count' => [
            'title' => 'Enrolments by stage',
            'intro' => 'Count of new enrolment requests grouped by school/scout stage (sana marhala).',
            'steps' => [
                'Read each stage name in the first column.',
                'Check the current count next to it.',
                'Use the totals to see which stages are busiest.',
                'Go back to the enrolment list to act on specific requests.',
            ],
        ],
        'person.new-enrolments-migrate-index' => [
            'title' => 'Migrate approved enrolments',
            'intro' => 'Review new applicants and migrate approved ones into the main person system by sector.',
            'steps' => [
                'Browse the list of new enrolment requests.',
                'Approve applicants who are ready if they are not approved yet.',
                'Use the sector migration links at the bottom to move all approved people for that sector into the main system.',
                'Confirm you chose the correct sector before running a bulk migrate.',
            ],
        ],
        'person.new-enrolments-qetaat-count' => [
            'title' => 'Enrolments by sector',
            'intro' => 'Count of new enrolment requests grouped by scout sector (qetaa).',
            'steps' => [
                'Read each sector and its request count.',
                'Compare sectors to see where load is highest.',
                'Use the numbers when planning approvals or capacity.',
                'Open the main enrolment list to process individual requests.',
            ],
        ],
        'person.new-enrolments-show' => [
            'title' => 'View enrolment request',
            'intro' => 'Read the full new-enrolment application: personal data and the answers to entry questions.',
            'steps' => [
                'Review Part 1 personal information and contact details.',
                'Check stage, sector, and other registration fields.',
                'Read the applicant’s answers to entry questions.',
                'Return to the list to approve, edit, or reject the request.',
            ],
        ],
        'person.show' => [
            'title' => 'View member data',
            'intro' => 'Read-only view of a member’s full registered profile, including photos and enrolment-style sections.',
            'steps' => [
                'Check personal details, contacts, and national ID.',
                'Review stage, sector, and other scout fields.',
                'Look at available personal or scout photos.',
                'Go back to the list or open Edit if you need changes and have permission.',
            ],
        ],
        'person.waiting-list-index' => [
            'title' => 'Waiting list',
            'intro' => 'Manage applicants waiting because sector capacity was full. You can move them into enrolment or reject them.',
            'steps' => [
                'Search or filter the waiting list by sector and other fields.',
                'Open View to read the waiting person’s details.',
                'Use Move to enrolment when a place becomes available.',
                'Use Reject only if the request should be removed permanently.',
            ],
        ],
        'person.waiting-list-show' => [
            'title' => 'Waiting person details',
            'intro' => 'Review one waiting-list applicant, then move them to enrolment or reject the request.',
            'steps' => [
                'Confirm name, sector, and stage in the header.',
                'Read personal and contact information carefully.',
                'Choose Move to enrolment if a place is available.',
                'Choose Reject request only after confirming the decision.',
            ],
        ],
        'personblacklist.create' => [
            'title' => 'Add to blacklist',
            'intro' => 'Select a person and record why they are being added to the blacklist.',
            'steps' => [
                'Search by name, ID, or phone and select the person.',
                'Write a clear note explaining the case.',
                'Double-check you selected the correct person.',
                'Submit to add them to the blacklist.',
            ],
        ],
        'personblacklist.edit' => [
            'title' => 'Edit blacklist entry',
            'intro' => 'Update the note or details of an existing blacklist case.',
            'steps' => [
                'Confirm the person shown is correct.',
                'Update the note if the case information changed.',
                'Save the changes.',
                'Return to the blacklist list to continue review.',
            ],
        ],
        'personblacklist.index' => [
            'title' => 'Blacklist',
            'intro' => 'Manage people placed on the blacklist, including notes and who recorded each case.',
            'steps' => [
                'Search the blacklist when looking for someone.',
                'Use Add to blacklist to create a new case.',
                'Open Edit to update the note or details.',
                'Use Delete only when a blacklist entry should be removed.',
            ],
        ],
        'personexammark.create' => [
            'title' => 'Record exam mark',
            'intro' => 'Enter theoretical and practical marks for a served member for a specific sector and stage year. Marks are whole numbers and may exceed 100.',
            'steps' => [
                'Search and select the served member (makhdoom).',
                'Choose the sector and stage year at exam time.',
                'Enter theoretical and practical marks as whole numbers.',
                'Add the exam date and optional note, then save.',
            ],
        ],
        'personexammark.edit' => [
            'title' => 'Edit exam mark',
            'intro' => 'Correct a previously recorded exam mark or its related details.',
            'steps' => [
                'Confirm the served member is correct.',
                'Update theoretical or practical marks if needed.',
                'Adjust sector, stage year, exam date, or note when required.',
                'Save the corrected mark.',
            ],
        ],
        'personexammark.index' => [
            'title' => 'Exam marks',
            'intro' => 'Browse recorded theoretical and practical exam marks for served members.',
            'steps' => [
                'Search for a member or mark record.',
                'Use Record new mark to add scores.',
                'Open Edit to correct an existing mark.',
                'Check sector, stage year, exam date, and who recorded the mark.',
            ],
        ],
        'personspecialcase.create' => [
            'title' => 'Add special case',
            'intro' => 'Attach a special-case note to a person in the system.',
            'steps' => [
                'Search and select the correct person.',
                'Write a clear note describing the special case.',
                'Confirm the selection before submit.',
                'Save to add the special case.',
            ],
        ],
        'personspecialcase.edit' => [
            'title' => 'Edit special case',
            'intro' => 'Update the note or details of an existing special-case record.',
            'steps' => [
                'Confirm the person linked to the case.',
                'Edit the note as needed.',
                'Save the updated special case.',
                'Return to the special cases list.',
            ],
        ],
        'personspecialcase.index' => [
            'title' => 'Special cases',
            'intro' => 'Track special-case notes attached to people, including who recorded them and when.',
            'steps' => [
                'Search for a person or case note.',
                'Use Add special case to create a new record.',
                'Open Edit to update an existing case note.',
                'Delete a case only when it should no longer be kept.',
            ],
        ],
        'place.create' => [
            'title' => 'Add place',
            'intro' => 'Create a new bookable place linked to a location.',
            'steps' => [
                'Choose the parent location.',
                'Enter the place name.',
                'Press Add place to save.',
            ],
        ],
        'place.edit' => [
            'title' => 'Edit place',
            'intro' => 'Update a place’s name or parent location.',
            'steps' => [
                'Change the location if the place moved.',
                'Update the place name.',
                'Press Edit place to save.',
            ],
        ],
        'place.index' => [
            'title' => 'Manage places',
            'intro' => 'List bookable places and which location each place belongs to.',
            'steps' => [
                'Search or sort the places table.',
                'Press Add place to create a new place under a location.',
                'Use Edit to rename a place or change its location.',
                'Use Delete to remove a place you no longer need.',
            ],
        ],
        'place_bookings.create' => [
            'title' => 'New place booking',
            'intro' => 'Request a place: choose location then place, set date and time range, then submit.',
            'steps' => [
                'Select location first so places for that location load.',
                'Choose the place and optionally a sector.',
                'Set booking date, From time, and To time.',
                'Add an optional note and press Send request (Pending review).',
            ],
        ],
        'place_bookings.edit' => [
            'title' => 'Edit place booking',
            'intro' => 'Change a booking request only while it is still pending review.',
            'steps' => [
                'Update location and place (places reload after location change).',
                'Adjust sector, date, and From/To times as needed.',
                'Update your note if needed.',
                'Press Save changes.',
            ],
        ],
        'place_bookings.my' => [
            'title' => 'My place booking requests',
            'intro' => 'Track your place booking requests: location, place, date/time, status, and reviewer.',
            'steps' => [
                'Scan the list for date, time, location, place, and status.',
                'Press New booking request to create one.',
                'Open View for full details and any admin changes.',
                'While Pending review, you can Edit or Delete the request.',
            ],
        ],
        'place_bookings.show' => [
            'title' => 'Place booking details',
            'intro' => 'See request status, location/place, notes, and any admin-approved changes.',
            'steps' => [
                'Check status and badges for location, place, sector, and reviewer.',
                'If reviewed, open Review result for approved place/times or rejection notes.',
                'Read your note and the admin note when present.',
                'If still pending, Edit or Delete; otherwise Back to your list.',
            ],
        ],
        'profile.edit' => [
            'title' => 'Edit profile',
            'intro' => 'Update your personal information. Shamandora code and national ID stay locked.',
            'steps' => [
                'Upload a personal or scout-uniform photo if you want to change them.',
                'Edit names, contact, address, and other allowed fields carefully.',
                'Press Save changes before leaving the form.',
                'Use the Change password section separately with a new password and confirmation.',
            ],
        ],
        'profile.show' => [
            'title' => 'My profile',
            'intro' => 'View your scout profile: contact data, study info, attendance summary, custody, and bookings.',
            'steps' => [
                'Confirm your name, Shamandora code, rank, and sector in the header.',
                'Switch tabs (Personal, Study, Attendance, Custody, Bookings) to review each section.',
                'Press Edit profile when you need to update details or change your password.',
                'Use attendance and related summaries as a quick personal status check.',
            ],
        ],
        'qetaa.auxiliary' => [
            'title' => 'View patrols',
            'intro' => 'Read-only browse of patrols (talaea) and their members after you pick a sector and team.',
            'steps' => [
                'Select a sector from the first dropdown.',
                'Then select a team, or Direct patrols when that option appears.',
                'Review each patrol card and the people listed under it.',
                'Use the summary counts (sectors, teams, patrols, people) to check coverage.',
            ],
        ],
        'qetaa.create' => [
            'title' => 'Add scout sector',
            'intro' => 'Create a new sector name in the system constants.',
            'steps' => [
                'Enter the sector name in the field.',
                'Check spelling carefully; this name appears in menus and member data.',
                'Press Add sector to save.',
                'You return to the sectors list after a successful save.',
            ],
        ],
        'qetaa.edit' => [
            'title' => 'Edit scout sector',
            'intro' => 'Update an existing sector’s display name.',
            'steps' => [
                'Change the sector name as needed.',
                'Confirm you selected the correct sector before saving.',
                'Press Save / Edit to apply the change.',
                'Existing links to this sector keep the same ID; only the name updates.',
            ],
        ],
        'qetaa.index' => [
            'title' => 'Scout sectors',
            'intro' => 'Admin list of scout sectors (qetaat) used across the system.',
            'steps' => [
                'Browse or search the sector table by name or ID.',
                'Sort columns or move between pages if the list is long.',
                'Use Add sector to create a new sector name.',
                'Use Edit or Delete on a row when you need to change or remove a sector.',
            ],
        ],
        'qetaa.tree' => [
            'title' => 'Team structure',
            'intro' => 'Interactive tree of sectors, teams (fareeq), patrols (taleia), and people for the season you serve in.',
            'steps' => [
                'Choose a season (and a sector if you serve more than one) from the top filters.',
                'Expand a sector to see teams and direct patrols; use Expand all / Collapse all as needed.',
                'Search by sector, group, or person name to highlight matches in the tree.',
                'When allowed, add a team or direct patrol, assign people by Shamandora code or name, or update a person’s rank.',
                'Open the ungrouped-people list for a selected sector to see members not yet in a group.',
            ],
        ],
        'role.create' => [
            'title' => 'Add role / duty',
            'intro' => 'Create a new role or duty name.',
            'steps' => [
                'Enter the role/duty name.',
                'Keep names short and recognizable for leaders.',
                'Press Add role/duty to save.',
                'Link it to people from the dedicated linking page when ready.',
            ],
        ],
        'role.edit' => [
            'title' => 'Edit role / duty',
            'intro' => 'Update an existing role or duty name.',
            'steps' => [
                'Change the role/duty name as needed.',
                'Confirm you selected the correct record.',
                'Press Save to apply.',
                'Existing person links keep the same role ID.',
            ],
        ],
        'role.index' => [
            'title' => 'Roles & duties',
            'intro' => 'Reference list of role/duty names that can later be linked to people.',
            'steps' => [
                'Browse or search roles and duties by name.',
                'Add a new role/duty when a new responsibility title is needed.',
                'Edit a name to keep wording consistent.',
                'Use Link roles & duties separately to assign these titles to people.',
            ],
        ],
        'rotab.create' => [
            'title' => 'Add scout rank',
            'intro' => 'Create a new scout rank name.',
            'steps' => [
                'Enter the rank name.',
                'Use the same style as existing ranks.',
                'Press Add to save.',
                'The new rank becomes available when assigning ranks in the team tree.',
            ],
        ],
        'rotab.edit' => [
            'title' => 'Edit scout rank',
            'intro' => 'Rename an existing scout rank.',
            'steps' => [
                'Update the rank name.',
                'Confirm the correct rank ID before saving.',
                'Press Save to apply.',
                'People already on this rank will show the new name.',
            ],
        ],
        'rotab.index' => [
            'title' => 'Scout ranks',
            'intro' => 'Lookup table of scout ranks (rotab) assigned to members in groups and the team tree.',
            'steps' => [
                'Browse or search ranks by name or ID.',
                'Add a rank when a new rank title is needed.',
                'Edit a rank name to keep titles consistent.',
                'Avoid deleting ranks still used on people in the team structure.',
            ],
        ],
        'sana-marhala.create' => [
            'title' => 'Add year & academic stage',
            'intro' => 'Create a detailed academic year/stage label.',
            'steps' => [
                'Enter the detailed stage name as it should appear to users.',
                'Match the wording used in school or university contexts.',
                'Press Add to save.',
                'Verify it on the Years & academic stages list.',
            ],
        ],
        'sana-marhala.edit' => [
            'title' => 'Edit year & academic stage',
            'intro' => 'Update a detailed academic year/stage label.',
            'steps' => [
                'Edit the displayed name.',
                'Keep changes clear for enrolment and profile forms.',
                'Press Save to apply.',
                'Existing selections keep the same ID with the new text.',
            ],
        ],
        'sana-marhala.index' => [
            'title' => 'Years & academic stages',
            'intro' => 'Detailed year-and-stage labels (sana marhala) shown on forms and member profiles.',
            'steps' => [
                'Browse or search the detailed stage names.',
                'Add a new detailed label when enrolment forms need a finer option.',
                'Edit a label to fix spelling or wording.',
                'Prefer this list over broad stages when the UI asks for year & stage.',
            ],
        ],
        'season-event.create' => [
            'title' => 'Link season to events',
            'intro' => 'Attach one or more events to a season so they can be used in bookings and finance.',
            'steps' => [
                'Choose the season from the dropdown.',
                'Select one or more events (Ctrl/Command + click for multiple).',
                'Press Link to create the season–event connections.',
            ],
        ],
        'season-event.edit' => [
            'title' => 'Edit season–event link',
            'intro' => 'Change which season and event are connected in an existing link.',
            'steps' => [
                'Select the correct season.',
                'Select the correct event (with its date range).',
                'Press Save changes to update the link.',
            ],
        ],
        'season-event.index' => [
            'title' => 'Season–event links',
            'intro' => 'See which events are linked to which seasons for booking and finance workflows.',
            'steps' => [
                'Search or filter by season, year, or event type.',
                'Press Add new link to attach one or more events to a season.',
                'Use Edit to change the season or event on a link.',
                'Use Delete to remove a season–event link.',
            ],
        ],
        'season.create' => [
            'title' => 'Add season',
            'intro' => 'Create a new season with an optional name and a required year.',
            'steps' => [
                'Optionally enter a season name.',
                'Enter the season year (required).',
                'Press Add season to save.',
            ],
        ],
        'season.edit' => [
            'title' => 'Edit season',
            'intro' => 'Update the name and year of an existing season.',
            'steps' => [
                'Change the season name if needed.',
                'Update the season year.',
                'Press Update season to save.',
            ],
        ],
        'season.index' => [
            'title' => 'Seasons',
            'intro' => 'Manage scout seasons (name and year) used across events and finance.',
            'steps' => [
                'Search or sort the seasons list by name or year.',
                'Press Add new season to create a season.',
                'Use Edit to change season name or year.',
                'Use Delete to remove a season that is no longer needed.',
            ],
        ],
        'secretary.create' => [
            'title' => 'Upload meeting document',
            'intro' => 'Upload a new leaders’ meeting document with its meeting date.',
            'steps' => [
                'Set the meeting date.',
                'Choose the document file to upload.',
                'Press Upload and save.',
                'Cancel if you need to return without uploading.',
            ],
        ],
        'secretary.edit' => [
            'title' => 'Edit document name',
            'intro' => 'Change the display name of an existing meeting document.',
            'steps' => [
                'Update the document name field.',
                'Press Edit to save.',
                'Confirm the new name in the documents list.',
            ],
        ],
        'secretary.index' => [
            'title' => 'Meeting documents',
            'intro' => 'Manage leaders’ meeting documents: download, edit names, or add new files.',
            'steps' => [
                'Browse documents by ID and name.',
                'Use Download to get a document file.',
                'Add a new document after a leaders’ meeting.',
                'Edit the document name or delete obsolete files if allowed.',
            ],
        ],
        'university.create' => [
            'title' => 'Add university',
            'intro' => 'Create a new university name.',
            'steps' => [
                'Enter the university name.',
                'Prefer the official institutional name.',
                'Press Add to save.',
                'Confirm it on the universities list.',
            ],
        ],
        'university.edit' => [
            'title' => 'Edit university',
            'intro' => 'Update an existing university name.',
            'steps' => [
                'Correct the university name.',
                'Confirm you selected the right university.',
                'Press Save to apply.',
                'Linked member records keep the same university ID.',
            ],
        ],
        'university.index' => [
            'title' => 'Universities',
            'intro' => 'Lookup of university names used with member education and registration fields.',
            'steps' => [
                'Browse or search universities by name.',
                'Add a university when applicants need a new option.',
                'Edit a university name to keep the list accurate.',
                'Avoid deleting universities still selected on member records.',
            ],
        ],
        'whatsapp.campaigns.create' => [
            'title' => 'Create WhatsApp campaign',
            'intro' => 'Build a draft campaign from the member directory with one message template and send-rate limits.',
            'steps' => [
                'Enter the campaign name and delay / per-hour limits.',
                'Filter and search members, then select recipients (or select all matching up to 2000).',
                'Write the message template; type { to insert variables such as {name}.',
                'Save the draft, then open the campaign page to confirm and send.',
            ],
        ],
        'whatsapp.campaigns.create-csv' => [
            'title' => 'WhatsApp campaign from CSV',
            'intro' => 'Upload a CSV of phone numbers and per-row messages (Egypt +20 is applied automatically). Max 2000 rows.',
            'steps' => [
                'Download the CSV template with Phone Number and Message columns.',
                'Fill numbers (local or with leading 0) and each personalized message.',
                'Enter a campaign name, choose delay/rate limits, and upload the file.',
                'Create the draft, then open the campaign to review and confirm sending.',
            ],
        ],
        'whatsapp.campaigns.edit' => [
            'title' => 'Edit WhatsApp campaign draft',
            'intro' => 'Update an editable campaign draft (name, recipients, template, and rate limits) before sending.',
            'steps' => [
                'Adjust campaign details and send-rate settings as needed.',
                'Re-search and change the selected recipients.',
                'Update the message template and missing-variable behavior.',
                'Save, then go to the campaign page to confirm send when ready.',
            ],
        ],
        'whatsapp.campaigns.index' => [
            'title' => 'WhatsApp campaigns',
            'intro' => 'Browse campaign drafts and sends, and start a new campaign from the member directory or a CSV file.',
            'steps' => [
                'Review each campaign’s status, recipient count, and creation time.',
                'Open View to manage sending or inspect recipients.',
                'Use Campaign from directory for one shared template, or Campaign from CSV for per-number messages.',
                'Download the CSV template before building a CSV campaign.',
            ],
        ],
        'whatsapp.campaigns.show' => [
            'title' => 'WhatsApp campaign details',
            'intro' => 'Monitor a campaign’s counters, template, and each recipient’s send status; start, pause, resume, or cancel when allowed.',
            'steps' => [
                'Check totals for pending, sent, failed, skipped, and cancelled.',
                'Read the template and delay/rate settings before sending.',
                'Confirm and send when ready (acknowledge large recipient counts if prompted).',
                'Use Pause, Resume, or Cancel as needed, and scan the recipient table for errors.',
            ],
        ],
        'whatsapp.status' => [
            'title' => 'WhatsApp bridge status',
            'intro' => 'Check whether the WhatsApp bridge is reachable and connected; scan a QR code if the session is waiting.',
            'steps' => [
                'Note the bridge URL and connection badge (Unavailable, Waiting for QR, or Connected).',
                'If a QR is shown, scan it from WhatsApp → Linked Devices.',
                'Press Refresh after scanning or if the status looks stale.',
                'When connected, keep the bridge auth session folder intact so the login persists.',
            ],
        ],
    ],
];
