#!/usr/bin/env python3
"""Translate priority Blade views: Arabic UI → __('English key') + lang parity."""

from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
LANG_EN = ROOT / "lang" / "en.json"
LANG_AR = ROOT / "lang" / "ar.json"

FILES = [
    "resources/views/event_booking_finance/show.blade.php",
    "resources/views/event_booking_finance/create_guest_family.blade.php",
    "resources/views/event_booking_finance/print_receipt.blade.php",
    "resources/views/event_booking_finance/create.blade.php",
    "resources/views/event_booking_finance/create_installment.blade.php",
    "resources/views/event_booking_finance/partial_refund.blade.php",
    "resources/views/event_booking_finance/edit_last_payment.blade.php",
    "resources/views/event_booking_finance/refund.blade.php",
    "resources/views/event_booking_finance/selector.blade.php",
    "resources/views/event_booking_finance/index.blade.php",
    "resources/views/finance/index.blade.php",
    "resources/views/finance/create.blade.php",
    "resources/views/finance/edit.blade.php",
    "resources/views/event/index.blade.php",
    "resources/views/event/create.blade.php",
    "resources/views/event/edit.blade.php",
    "resources/views/event_waiting_list/index.blade.php",
    "resources/views/medicine/index.blade.php",
    "resources/views/medicine/dispense.blade.php",
    "resources/views/medicine/locks.blade.php",
    "resources/views/games/index.blade.php",
    "resources/views/games/create.blade.php",
    "resources/views/games/edit.blade.php",
    "resources/views/inventory-issue/index.blade.php",
    "resources/views/attendance/manage.blade.php",
]

# Arabic phrase → English JSON key (source language). Longer phrases first at apply time.
MAP: dict[str, str] = {
    # long / unique messages
    "إذا كانت أول دفعة أقل من الحد الأدنى للمقدم سيتم عرض تنبيه فقط ويمكنك الاستمرار.": "If the first payment is below the minimum deposit, only a warning is shown and you can continue.",
    "ادخل اسم الحدث أو المناسبة (اختياري - سيتم تكوينه تلقائياً إذا تركته فارغاً)": "Enter event or occasion name (optional — auto-generated if left empty)",
    "هذه الفعالية تحتوي على قسط واحد فقط، لذلك يجب دفع كامل المبلغ في أول دفعة.": "This event has only one installment, so the full amount must be paid in the first payment.",
    "مثال: إذا كان المدفوع 1000 وكتبت 200، سيتم استرداد 800 والاحتفاظ بـ 200.": "Example: if paid is 1000 and you enter 200, 800 will be refunded and 200 kept.",
    "قم بتحديد الجزء الذي سيتم خصمه، وسيتم استرداد باقي المبلغ للملتحق.": "Specify the amount to deduct; the remaining paid amount will be refunded to the member.",
    "اختر الموسم والفعالية، أضف الأصناف والكميات، ثم اطبع بشكل احترافي": "Choose season and event, add items and quantities, then print professionally",
    "لم يتم اختيار تاريخ النهاية. هل تريد جعله نفس تاريخ البداية؟": "End date not selected. Do you want to set it to the same as the start date?",
    "هذه آخر دفعة، لذلك تم ضبط المبلغ تلقائيًا على كامل المتبقي.": "This is the last payment, so the amount was set automatically to the full remaining balance.",
    "هذه الدفعة أقل من الحد الأدنى للمقدم. هل تريد الاستمرار؟": "This payment is below the minimum deposit. Do you want to continue?",
    "اختر الموسم والفعالية المصرح لك بها ثم سجّل حضور الأفراد": "Choose the season and authorized event, then record individual attendance",
    "متابعة حجوزات الفعالية وإدارة الأشخاص والضيوف والأهالي": "Follow up event bookings and manage people, guests, and families",
    "هناك أيام مكررة. برجاء اختيار كل يوم مرة واحدة فقط.": "There are duplicate days. Please choose each day only once.",
    "تاريخ بداية الحدث يجب أن يكون قبل تاريخ النهاية": "Event start date must be before the end date",
    "تأكد من اختيار فعالية وإضافة أصناف قبل الطباعة.": "Make sure to choose an event and add items before printing.",
    "اكتب الاسم أو الكود أو الموبايل أو الرقم القومي": "Type name, code, mobile, or national ID",
    "يرجى تعبئة جميع الأيام أو حذف الصفوف الفارغة": "Please fill all days or delete empty rows",
    "هل أنت متأكد من استرداد كل المبلغ المدفوع؟": "Are you sure you want to refund the full paid amount?",
    "هذا اليوم مكرر بالفعل. اختر يومًا مختلفًا.": "This day is already duplicated. Choose a different day.",
    "هذه آخر دفعة ويجب أن تساوي كامل المتبقي.": "This is the last payment and must equal the full remaining balance.",
    "لا توجد فعاليات تخص مجموعاتك في هذا الموسم.": "No events for your groups in this season.",
    "من فضلك أضف صنف واحد على الأقل قبل الطباعة.": "Please add at least one item before printing.",
    "اكتب الجزء الذي سيتم خصمه من المبلغ المدفوع": "Enter the portion to deduct from the paid amount",
    "الحجز لفرد من العائلة وسيظهر قطاعه اهالي": "Booking for a family member; sector will show as Families",
    "قم بتعديل بيانات اللعبة بشكل واضح ومنظم": "Edit the game details clearly and in an organized way",
    "قم بإدخال بيانات اللعبة بشكل واضح ومنظم": "Enter the game details clearly and in an organized way",
    "اسم الحدث أو المناسبة الكشفية (اختياري)": "Scout event or occasion name (optional)",
    "اختيار الفعالية لإدارة الحجوزات المالية": "Choose event to manage booking finance",
    "نصيحة: اكتب حرفين أو أكثر لنتائج أسرع": "Tip: type two or more characters for faster results",
    "بحث: الاسم / الهاتف / القطاع / المرحلة": "Search: name / phone / sector / stage",
    "لا يمكن تكرار نفس اليوم أكثر من مرة.": "The same day cannot be repeated more than once.",
    "ادخل اسم الحدث أو المناسبة (اختياري)": "Enter event or occasion name (optional)",
    "ابحث بالاسم أو PersonID أو الموبايل": "Search by name, PersonID, or mobile",
    "اكتب الاسم أو الكود أو رقم الموبايل": "Type name, code, or mobile number",
    "الحد الأقصى لعدد الأقساط في الخطة:": "Max installments in the plan:",
    "اكتب اسم الدواء أو النوع أو المكان": "Type medicine name, type, or place",
    "اختر نوع الحدث أو المناسبة الكشفية": "Choose scout event type",
    "اختر القطاعات المربوطة بهذا الحدث": "Choose sectors linked to this event",
    "لا يمكن تكوين اسم الحدث تلقائياً": "Cannot auto-generate event name",
    "لا يمكن تكوين اسم الحدث تلقائياً قبل اختيار نوع الحدث والقطاع والتاريخ": "Cannot auto-generate event name before choosing event type, sector, and date",
    "اختر الموسم أولاً لعرض الفعاليات": "Choose season first to show events",
    "إحصائيات اليوم المحدد + الإجمالي": "Selected day stats + totals",
    "يرجى اختيار قطاع واحد على الأقل": "Please choose at least one sector",
    "يرجى ملء جميع الحقول المطلوبة": "Please fill all required fields",
    "يرجى إضافة يوم واحد على الأقل": "Please add at least one day",
    "يرجى إضافة يوم واحد على الأقل عند اختيار \"متكرر\"": 'Please add at least one day when choosing "Recurring"',
    "نوع الحدث أو المناسبة الكشفية": "Scout event or occasion type",
    "حدث خطأ أثناء تحميل الفعاليات": "Error loading events",
    "إدارة الخطط المالية للفعاليات": "Manage event finance plans",
    "من فضلك اختر الفعالية أولاً.": "Please choose the event first.",
    "حدث خطأ أثناء تجهيز الطباعة.": "An error occurred while preparing print.",
    "الحجز لضيف وسيظهر قطاعه ضيوف": "Guest booking; sector will show as Guests",
    "إضافة شخص إلى قائمة الانتظار": "Add person to waiting list",
    "لا توجد فعاليات لهذا الموسم": "No events for this season",
    "إدارة قائمة انتظار الفعالية": "Manage event waiting list",
    "غير قادر على دفع كل المبلغ": "Unable to pay the full amount",
    "لا يوجد مكان به كمية متاحة": "No location has available quantity",
    "يمكن طباعة إيصال لكل دفعة": "A receipt can be printed for each payment",
    "إنشاء حجز جديد + أول دفعة": "Create new booking + first payment",
    "استرداد المبلغ مع خصم جزء": "Refund amount with partial deduction",
    "لم يتم اختيار أي شخص بعد": "No person selected yet",
    "لم يتم اختيار أي صنف بعد.": "No item selected yet.",
    "إضافة إلى قائمة الانتظار": "Add to waiting list",
    "تم تحميل الفعاليات بنجاح": "Events loaded successfully",
    "يرجى إدخال تاريخ البداية": "Please enter the start date",
    "عدد الحجوزات حسب القطاع": "Bookings count by sector",
    "جاري تحميل الفعاليات...": "Loading events...",
    "السعر الفعلي بعد الخصم:": "Actual price after discount:",
    "تم تجهيز الطباعة بنجاح.": "Print prepared successfully.",
    "إضافة خطة مالية لفعالية": "Add finance plan for an event",
    "-- اختر الدواء أولاً --": "-- Choose medicine first --",
    "عرض سريع لحالة كل قطاع": "Quick view of each sector status",
    "ملاحظات إضافية إن وجدت": "Additional notes if any",
    "هذا الصنف مضاف بالفعل.": "This item is already added.",
    "تمت إضافة الصنف بنجاح.": "Item added successfully.",
    "- لها خطة مالية بالفعل": "- already has a finance plan",
    "اضافة حدث/مناسبة جديدة": "Add new event/occasion",
    "إضافة حجز ضيف / أهالي": "Add guest / family booking",
    "تاريخ انتهاء المناسبة": "Occasion end date",
    "جاري تجهيز الطباعة...": "Preparing print...",
    "ستظهر في أسفل كل صفحة": "Will appear at the bottom of each page",
    "أي ملاحظات على الحجز": "Any notes on the booking",
    "إجمالي المدفوع الآن:": "Total paid now:",
    "ادخل العهدة المطلوبة": "Enter required custody items",
    "ادخل الهدف من اللعبة": "Enter the game objective",
    "الحد الأقصى للأقساط:": "Max installments:",
    "لا توجد دفعات مسجلة.": "No payments recorded.",
    "يسمح بأقل من المقدم": "Allow below minimum deposit",
    "١) الموسم والفعالية": "1) Season and event",
    "٣) الأصناف المختارة": "3) Selected items",
    "-- اختر الفعالية --": "-- Choose event --",
    "اختر الفعالية أولاً": "Choose the event first",
    "ادخل الرابط المرجعي": "Enter reference link",
    "اكتب اسم المُستَلِم": "Enter recipient name",
    "اكتب اسم المُسَلِّم": "Enter issuer name",
    "الحد الأدنى للمقدم:": "Minimum deposit:",
    "تعديل الخطة المالية": "Edit finance plan",
    "تفاصيل جميع الدفعات": "All payments details",
    "حدث خطأ أثناء البحث": "An error occurred while searching",
    "إضافة مناسبة جديدة": "Add new occasion",
    "ادخل الفئة العمرية": "Enter age group",
    "ادخل قوانين اللعبة": "Enter game rules",
    "استرداد مع خصم جزء": "Partial refund with deduction",
    "الحد الأدنى للمقدم": "Minimum deposit",
    "تاريخ بدء المناسبة": "Occasion start date",
    "حفظ وطباعة الإيصال": "Save and print receipt",
    "قابل للتعديل/الحذف": "Editable/deletable",
    "مكان الدواء": "Medicine place",
    "نظام النقاط": "Points system",
    "أضافه الخادم": "Added by servant",
    "إجمالي الحجز": "Booking total",
    "إضافة اللعبة": "Add game",
    "ابحث ثم اختر": "Search then select",
    "استرداد كامل": "Full refund",
    "اسم المناسبة": "Occasion name",
    "السعر الأصلي": "Original price",
    "بيانات الحجز": "Booking details",
    "تحديث المقاس": "Update size",
    "تسجيل الحضور": "Record attendance",
    "تعديل اللعبة": "Edit game",
    "رقم الإيصال:": "Receipt number:",
    "رقم المناسبة": "Occasion ID",
    "عدد الأقساط:": "Installments count:",
    "عدد الحجوزات": "Bookings count",
    "عنوان اللعبة": "Game title",
    "محجوز بالفعل": "Already booked",
    "ملاحظات عامة": "General notes",
    "نسخة الماليه": "Finance copy",
    "نسخة المشترك": "Participant copy",
    "نوع العملية:": "Transaction type:",
    "نوع المناسبة": "Occasion type",
    "وقت الإصدار:": "Issued at:",
    "💾 حفظ الحضور": "💾 Save attendance",
    "أماكن الأدوية": "Medicine locations",
    "إجراءات سريعة": "Quick actions",
    "إدارة الألعاب": "Manage games",
    "إضافة حجز شخص": "Add person booking",
    "ابحث عن الصنف": "Search for item",
    "اكتب العذر...": "Write the excuse...",
    "الجزء المخصوم": "Deducted portion",
    "السعر الأصلي:": "Original price:",
    "القسط الحالي:": "Current installment:",
    "المبلغ الجديد": "New amount",
    "الملخص السريع": "Quick summary",
    "تاريخ الإضافة": "Added at",
    "تاريخ الدفعة:": "Payment date:",
    "توزيع المحجوز": "Locked distribution",
    "جاري البحث...": "Searching...",
    "حفظ التعديلات": "Save changes",
    "طباعة الإيصال": "Print receipt",
    "مبلغ أول دفعة": "First payment amount",
    "مقاس التيشيرت": "T-shirt size",
    "أقصى عدد أقساط": "Max installments",
    "إجمالي المخزون": "Total stock",
    "إنشاء حجز جديد": "Create new booking",
    "اختر يوم الدفع": "Choose payment day",
    "المبلغ الحالي:": "Current amount:",
    "المبلغ المدفوع": "Paid amount",
    "بداية الفعالية": "Event start",
    "بدون ربط بموسم": "Not linked to a season",
    "بيانات الإيصال": "Receipt details",
    "بيانات العملية": "Transaction details",
    "بيانات المشترك": "Participant details",
    "تاريخ أول دفعة": "First payment date",
    "تاريخ الانتهاء": "Expiry date",
    "تاريخ العملية:": "Transaction date:",
    "تحميل CSV كامل": "Download full CSV",
    "تعديل آخر دفعة": "Edit last payment",
    "تغيير الفعالية": "Change event",
    "قائمة الحجوزات": "Bookings list",
    "نهاية الفعالية": "Event end",
    "إجمالي المدفوع:": "Total paid:",
    "إدارة المناسبات": "Manage occasions",
    "إضافة خطة مالية": "Add finance plan",
    "اختيار الفعالية": "Choose event",
    "ادخل وصف اللعبة": "Enter game description",
    "الاختيار الحالي": "Current selection",
    "البحث والاختيار": "Search and select",
    "الدفعات السابقة": "Previous payments",
    "الرقم التعريفي:": "ID number:",
    "الفترات السعرية": "Price intervals",
    "القائد المستلم:": "Receiving leader:",
    "المطلوب النهائي": "Final required",
    "بحث عن شخص مؤهل": "Search for eligible person",
    "تأكيد الاسترداد": "Confirm refund",
    "تحميل CSV اليوم": "Download today's CSV",
    "تنفيذ الاسترداد": "Process refund",
    "حجوزات الفعالية": "Event bookings",
    "سجل حجز الأدوية": "Medicine lock log",
    "طباعة آخر إيصال": "Print last receipt",
    "هل يوجد تيشيرت؟": "Has a T-shirt?",
    "إضافة حدث/مناسبة": "Add event/occasion",
    "إضافة دفعة جديدة": "Add new payment",
    "إضافة فترة سعرية": "Add price interval",
    "إضافة لعبة جديدة": "Add new game",
    "ادخل نظام النقاط": "Enter points system",
    "البيانات المالية": "Financial details",
    "المطلوب النهائي:": "Final required:",
    "الموسم (اختياري)": "Season (optional)",
    "تعديل حدث/مناسبة": "Edit event/occasion",
    "عرض بيانات الحجز": "View booking details",
    "٢) إضافة الأصناف": "2) Add items",
    "-- اختر القطاع --": "-- Choose sector --",
    "-- اختر المقاس --": "-- Choose size --",
    "-- اختر الموسم --": "-- Choose season --",
    "أخذ الحضور بواسطة": "Attendance taken by",
    "ادخل عنوان اللعبة": "Enter game title",
    "اكتب اسم الصنف...": "Type item name...",
    "تاريخ بداية الحجز": "Lock start date",
    "تاريخ بداية الحدث": "Event start date",
    "تاريخ نهاية الحجز": "Lock end date",
    "تاريخ نهاية الحدث": "Event end date",
    "تحديث مقاس القميص": "Update shirt size",
    "تحميل / طباعة PDF": "Download / print PDF",
    "حفظ الخطة المالية": "Save finance plan",
    "رقم القسط الحالي:": "Current installment number:",
    "٤) بيانات التوقيع": "4) Signature details",
    "أقصى عدد أقساط": "Max installments",
    "+ إضافة يوم": "+ Add day",
    "~ غائب بعذر": "~ Absent with excuse",
    "ابدأ من هنا": "Start here",
    "اختر القطاع": "Choose sector",
    "اختر المقاس": "Choose size",
    "اختر الموسم": "Choose season",
    "اسم الملتحق": "Member name",
    "تسجيل الصرف": "Record dispense",
    "تعيين الكل:": "Set all:",
    "رقم الإيصال": "Receipt number",
    "عدد الأقساط": "Installments count",
    "عدد الفترات": "Intervals count",
    "مثال: معسكر": "Example: camp",
    "هاتف الام": "Mother phone",
    "إضافة دفعة": "Add payment",
    "إضافة دواء": "Add medicine",
    "اسم الدواء": "Medicine name",
    "اسم الشخص:": "Person name:",
    "اسم اللعبة": "Game name",
    "المُستَلِم": "Recipient",
    "المُسَلِّم": "Issuer",
    "تعديل لعبة": "Edit game",
    "حجز الكمية": "Lock quantity",
    "حذف الفترة": "Delete interval",
    "رقم القسط:": "Installment number:",
    "رقم اللعبة": "Game ID",
    "رقم الهوية": "ID number",
    "طباعة عهدة": "Print custody",
    "غائب بعذر:": "Absent with excuse:",
    "مبلغ الخصم": "Discount amount",
    "مكان الحجز": "Lock location",
    "نوع الحالة": "Case type",
    "آخر دفعة": "Last payment",
    "أقل مقدم": "Minimum deposit",
    "أول دفعة": "First payment",
    "الفعالية": "Event",
    "القطاعات": "Sectors",
    "القوانين": "Rules",
    "المتبقي:": "Remaining:",
    "المدفوع:": "Paid:",
    "الموبايل": "Mobile",
    "بدون رقم": "No number",
    "فك الحجز": "Release lock",
    "إعادة ضبط": "Reset",
    "اسم الحدث": "Event name",
    "الإجمالي:": "Total:",
    "المناسبات": "Occasions",
    "تم بواسطة": "By",
    "حجز أدوية": "Reserve medicine",
    "حفظ الحجز": "Save booking",
    "رقم القسط": "Installment number",
    "سبب الحجز": "Lock reason",
    "سجل الصرف": "Dispense log",
    "نوع الحجز": "Booking type",
    "البداية": "Start",
    "التوزيع": "Distribution",
    "القطاع:": "Sector:",
    "المحجوز": "Locked",
    "المحصّل": "Collected",
    "المدفوع": "Paid",
    "المرتجع": "Refunded",
    "المستلم": "Recipient",
    "المطلوب": "Required amount",
    "النهاية": "End",
    "له إخوة": "Has brothers",
    "أخوه رب": "Brotherhood case",
    "استرداد": "Refund",
    "الألعاب": "Games",
    "✓ حاضر": "✓ Present",
    "✗ غائب": "✗ Absent",
    "اختيار": "Select",
    "التالي": "Next",
    "الحضور": "Attendance section",
    "الخادم": "Servant",
    "الخصم:": "Discount:",
    "السابق": "Previous",
    "الكمية": "Quantity",
    "المتاح": "Available",
    "المسلم": "Issuer",
    "المقاس": "Size",
    "فعالية": "Event",
    "حاضر:": "Present:",
    "طباعة": "Print",
    "غائب:": "Absent:",
    "متكرر": "Recurring",
    "محظور": "Blacklisted",
    "مرتجع": "Refunded",
    "مكتمل": "Full",
    "0 صنف": "0 items",
    "~ عذر": "~ Excuse",
    "أهالي": "Families",
    "ادخال": "Enter",
    "الخصم": "Discount",
    "السبب": "Reason",
    "السجل": "Log",
    "السعر": "Price",
    "العذر": "Excuse",
    "الهدف": "Objective",
    "الوصف": "Description",
    "تحديث": "Update",
    "تطبيق": "Apply",
    "توزيع": "Distribute",
    "ضيف": "Guest",
    "دخول": "Enter",
    "دفعة": "Payment",
    "عادي": "Normal",
    "وحدة": "Unit",
    "أخرى": "Other",
    "ابحث": "Search",
    "بدون": "None",
    "ملاحظة: لو آخر فترة انتهت قبل بداية الفعالية، سيتم استكمال الأيام المتبقية تلقائيًا بنفس آخر سعر.": "Note: if the last interval ends before the event starts, remaining days are filled automatically with the last price.",
    "في حالة ترك الاسم فارغًا سيتم تكوينه تلقائيًا من: نوع الحدث + القطاع + تاريخ البداية + تاريخ النهاية": "If the name is left empty it will be auto-generated from: event type + sector + start date + end date",
    "الأيام المختارة (لكل يوم سيتم إنشاء حدث منفصل يبدأ وينتهي في نفس اليوم)": "Selected days (for each day a separate event is created that starts and ends on the same day)",
    "الاسم التلقائي المقترح:": "Suggested auto name:",
    "بعد تسجيل أول دفعة، يمكن إضافة باقي الأقساط حتى": "After recording the first payment, remaining installments can be added up to",
    "أقساط.": "installments.",
    "السماح بأقل من المقدم:": "Allow below minimum deposit:",
    "السماح بأقل من المقدم": "Allow below minimum deposit",
    "أقصى عدد أقساط": "Max installments",
    "أقصى عدد": "Max number",
    "أقساط": "installments",
    "إجمالي يوم": "Day total",
    "قطاع": "sector",
    "الموسم:": "Season:",
    "نعم": "Yes",
    "لا": "No",
}

# Extra keys that need explicit en/ar (when key text differs from MAP value usage)
EXTRAS: dict[str, tuple[str, str]] = {
    "✓ Present": ("✓ Present", "✓ حاضر"),
    "✗ Absent": ("✗ Absent", "✗ غائب"),
    "Present:": ("Present:", "حاضر:"),
    "Absent:": ("Absent:", "غائب:"),
    "Absent with excuse:": ("Absent with excuse:", "غائب بعذر:"),
    "~ Absent with excuse": ("~ Absent with excuse", "~ غائب بعذر"),
    "~ Excuse": ("~ Excuse", "~ عذر"),
    "💾 Save attendance": ("💾 Save attendance", "💾 حفظ الحضور"),
    "Required amount": ("Required", "المطلوب"),
    "Paid": ("Paid", "المدفوع"),
    "Collected": ("Collected", "المحصّل"),
    "Refunded": ("Refunded", "المرتجع"),
    "Mobile": ("Mobile", "الموبايل"),
    "Size": ("Size", "المقاس"),
    "Locked": ("Locked", "المحجوز"),
    "Available": ("Available", "المتاح"),
    "Distribution": ("Distribution", "التوزيع"),
    "Locked distribution": ("Locked distribution", "توزيع المحجوز"),
    "Brotherhood case": ("Brotherhood case", "أخوه رب"),
    "Has brothers": ("Has brothers", "له إخوة"),
    "Other": ("Other", "أخرى"),
    "Normal": ("Normal", "عادي"),
    "Blacklisted": ("Blacklisted", "محظور"),
    "Already booked": ("Already booked", "محجوز بالفعل"),
    "Guest": ("Guest", "ضيف"),
    "Families": ("Families", "أهالي"),
    "Full": ("Full", "مكتمل"),
    "Recurring": ("Recurring", "متكرر"),
    "Payment": ("Payment", "دفعة"),
    "Refund": ("Refund", "استرداد"),
    "Print": ("Print", "طباعة"),
    "Apply": ("Apply", "تطبيق"),
    "Update": ("Update", "تحديث"),
    "Distribute": ("Distribute", "توزيع"),
    "Select": ("Select", "اختيار"),
    "Enter": ("Enter", "دخول"),
    "Start": ("Start", "البداية"),
    "End": ("End", "النهاية"),
    "By": ("By", "تم بواسطة"),
    "Reason": ("Reason", "السبب"),
    "Log": ("Log", "السجل"),
    "Price": ("Price", "السعر"),
    "Excuse": ("Excuse", "العذر"),
    "Objective": ("Objective", "الهدف"),
    "Description": ("Description", "الوصف"),
    "Rules": ("Rules", "القوانين"),
    "Points system": ("Points system", "نظام النقاط"),
    "Servant": ("Servant", "الخادم"),
    "Discount": ("Discount", "الخصم"),
    "Discount:": ("Discount:", "الخصم:"),
    "Original price": ("Original price", "السعر الأصلي"),
    "Original price:": ("Original price:", "السعر الأصلي:"),
    "Final required": ("Final required", "المطلوب النهائي"),
    "Final required:": ("Final required:", "المطلوب النهائي:"),
    "Paid:": ("Paid:", "المدفوع:"),
    "Remaining:": ("Remaining:", "المتبقي:"),
    "Total:": ("Total:", "الإجمالي:"),
    "Total paid:": ("Total paid:", "إجمالي المدفوع:"),
    "Total paid now:": ("Total paid now:", "إجمالي المدفوع الآن:"),
    "Mother phone": ("Mother phone", "هاتف الام"),
    "Added by servant": ("Added by servant", "أضافه الخادم"),
    "ID number": ("ID number", "رقم الهوية"),
    "ID number:": ("ID number:", "الرقم التعريفي:"),
    "Person name:": ("Person name:", "اسم الشخص:"),
    "Receipt number": ("Receipt number", "رقم الإيصال"),
    "Receipt number:": ("Receipt number:", "رقم الإيصال:"),
    "Issued at:": ("Issued at:", "وقت الإصدار:"),
    "Transaction type:": ("Transaction type:", "نوع العملية:"),
    "Transaction date:": ("Transaction date:", "تاريخ العملية:"),
    "Receiving leader:": ("Receiving leader:", "القائد المستلم:"),
    "Installment number": ("Installment number", "رقم القسط"),
    "Installment number:": ("Installment number:", "رقم القسط:"),
    "Installments count": ("Installments count", "عدد الأقساط"),
    "Installments count:": ("Installments count:", "عدد الأقساط:"),
    "Current installment:": ("Current installment:", "القسط الحالي:"),
    "Current installment number:": ("Current installment number:", "رقم القسط الحالي:"),
    "Payment date:": ("Payment date:", "تاريخ الدفعة:"),
    "Current amount:": ("Current amount:", "المبلغ الحالي:"),
    "Actual price after discount:": ("Actual price after discount:", "السعر الفعلي بعد الخصم:"),
    "Max installments:": ("Max installments:", "الحد الأقصى للأقساط:"),
    "Max installments in the plan:": ("Max installments in the plan:", "الحد الأقصى لعدد الأقساط في الخطة:"),
    "Minimum deposit:": ("Minimum deposit:", "الحد الأدنى للمقدم:"),
    "Allow below minimum deposit:": ("Allow below minimum deposit:", "السماح بأقل من المقدم:"),
    "Sector:": ("Sector:", "القطاع:"),
    "Finance copy": ("Finance copy", "نسخة الماليه"),
    "Participant copy": ("Participant copy", "نسخة المشترك"),
    "0 items": ("0 items", "0 صنف"),
    "installments": ("installments", "أقساط"),
    "installments.": ("installments.", "أقساط."),
    "sector": ("sector", "قطاع"),
    "Day total": ("Day total", "إجمالي يوم"),
    "Max number": ("Max number", "أقصى عدد"),
    "Suggested auto name:": ("Suggested auto name:", "الاسم التلقائي المقترح:"),
}


def wrap_php_string(ar: str, en: str, text: str) -> str:
    patterns = [
        (
            re.compile(
                r"(['\"](?:label|disabledLabel|title|name|placeholder|pageTitle)['\"]\s*=>\s*)(['\"])"
                + re.escape(ar)
                + r"\2"
            ),
            rf"\1__('{en}')",
        ),
        (
            re.compile(r"(pageTitle['\"]?\s*=>\s*)(['\"])" + re.escape(ar) + r"\2"),
            rf"\1__('{en}')",
        ),
        (
            re.compile(r"((?:title|placeholder|alt|aria-label)=)(['\"])" + re.escape(ar) + r"\2"),
            rf"\1\"{{{{ __('{en}') }}}}\"",
        ),
        (
            re.compile(r"(<(?:h[1-6]|p|span|div|label|button|a|th|td|strong|option|li)\b[^>]*>)\s*" + re.escape(ar) + r"\s*(</)"),
            rf"\1{{{{ __('{en}') }}}}\2",
        ),
        # plain text nodes already partially covered; also bare between tags with whitespace
        (
            re.compile(r">(\s*)" + re.escape(ar) + r"(\s*)<"),
            rf">\1{{{{ __('{en}') }}}}\2<",
        ),
    ]
    for rx, repl in patterns:
        text = rx.sub(repl, text)
    return text


def wrap_js_and_misc(ar: str, en: str, text: str) -> str:
    """Replace Arabic in JS string literals and Blade-interpolated contexts."""
    # Single/double quoted JS/Blade literals (not already __)
    def repl_quoted(m: re.Match) -> str:
        q = m.group(1)
        before = text[max(0, m.start() - 5) : m.start()]
        if "__(" in before:
            return m.group(0)
        # Keep form default values that are data (معسكر as old default)
        if ar == "معسكر" and "lock_reason" in text[max(0, m.start() - 80) : m.start()]:
            return m.group(0)
        return f"{q}{{{{ __('{en}') }}}}{q}"

    # Only exact quoted match
    text = re.sub(
        r"(['\"])" + re.escape(ar) + r"\1",
        repl_quoted,
        text,
    )

    # confirm('عربي') / alert('عربي')
    text = re.sub(
        r"(confirm|alert)\((['\"])" + re.escape(ar) + r"\2\)",
        rf"\1(@json(__('{en}')))",
        text,
    )
    return text


def ensure_lang(en: dict, ar: dict) -> None:
    for ar_text, en_key in MAP.items():
        en.setdefault(en_key, en_key)
        ar.setdefault(en_key, ar_text)
    for key, (en_v, ar_v) in EXTRAS.items():
        en.setdefault(key, en_v)
        ar.setdefault(key, ar_v)
    # Prefer canonical Arabic for shared keys already present
    overrides = {
        "Yes": "نعم",
        "No": "لا",
        "Search": "ابحث",
        "None": "بدون",
        "Next": "التالي",
        "Previous": "السابق",
        "Quantity": "الكمية",
        "Unit": "وحدة",
        "Event": "الفعالية",
        "Games": "الألعاب",
        "Sectors": "القطاعات",
        "Print custody": "طباعة عهدة",
        "Reserve medicine": "حجز أدوية",
        "Medicine locations": "أماكن الأدوية",
        "Manage games": "إدارة الألعاب",
        "Choose season": "اختر الموسم",
        "Choose event": "اختر الفعالية",
        "Choose scout event type": "اختر نوع الحدث أو المناسبة الكشفية",
        "Attendance section": "الحضور",
        "Reset": "إعادة ضبط",
        "Error loading events": "حدث خطأ أثناء تحميل الفعاليات",
    }
    for k, v in overrides.items():
        if k in ar:
            ar[k] = v
        if k in en:
            en.setdefault(k, k)


def process_file(path: Path) -> bool:
    original = path.read_text(encoding="utf-8")
    text = original
    sorted_map = sorted(MAP.items(), key=lambda kv: len(kv[0]), reverse=True)
    for ar, en in sorted_map:
        # skip ultra-short tokens that are dangerous except via exact tag/quote patterns already
        if len(ar) <= 1:
            continue
        text = wrap_php_string(ar, en, text)
        text = wrap_js_and_misc(ar, en, text)

    # Multiline-normalized replacements for known long notes inside <p>...</p>
    multiline_notes = [
        (
            "ملاحظة: لو آخر فترة انتهت قبل بداية الفعالية، سيتم استكمال الأيام المتبقية تلقائيًا بنفس آخر سعر.",
            "Note: if the last interval ends before the event starts, remaining days are filled automatically with the last price.",
        ),
        (
            "في حالة ترك الاسم فارغًا سيتم تكوينه تلقائيًا من: نوع الحدث + القطاع + تاريخ البداية + تاريخ النهاية",
            "If the name is left empty it will be auto-generated from: event type + sector + start date + end date",
        ),
    ]
    for ar, en in multiline_notes:
        # collapse whitespace variants
        rx = re.compile(re.escape(ar).replace(r"\ ", r"\s+"))
        text = rx.sub(f"{{{{ __('{en}') }}}}", text)

    if text != original:
        path.write_text(text, encoding="utf-8")
        return True
    return False


def main() -> None:
    en = json.loads(LANG_EN.read_text(encoding="utf-8"))
    ar = json.loads(LANG_AR.read_text(encoding="utf-8"))
    ensure_lang(en, ar)

    changed = []
    for rel in FILES:
        path = ROOT / rel
        if not path.exists():
            print("MISSING", rel)
            continue
        if process_file(path):
            changed.append(rel)

    # parity + sort
    for k in list(en):
        ar.setdefault(k, en[k])
    for k in list(ar):
        en.setdefault(k, k)
    # drop keys only in one? already synced
    en_sorted = {k: en[k] for k in sorted(en)}
    ar_sorted = {k: ar[k] for k in sorted(ar)}
    assert set(en_sorted) == set(ar_sorted)

    LANG_EN.write_text(json.dumps(en_sorted, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")
    LANG_AR.write_text(json.dumps(ar_sorted, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")

    print(f"Updated {len(changed)} files")
    for c in changed:
        print(" -", c)
    print(f"Lang keys: {len(en_sorted)}")


if __name__ == "__main__":
    main()
