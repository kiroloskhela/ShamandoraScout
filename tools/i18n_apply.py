#!/usr/bin/env python3
"""
Apply bilingual wrappers to Blade views using a known Arabic→English key map.
Safe: only replaces exact known Arabic phrases in common Blade/PHP quote contexts.
"""

from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
VIEWS = ROOT / "resources" / "views"
LANG_EN = ROOT / "lang" / "en.json"
LANG_AR = ROOT / "lang" / "ar.json"

# Arabic phrase → English JSON key (source language)
MAP: dict[str, str] = {
    # common actions
    "تعديل": "Edit",
    "مسح": "Delete",
    "حذف": "Delete",
    "عرض": "View",
    "حفظ": "Save",
    "إلغاء": "Cancel",
    "رجوع": "Back",
    "بحث": "Search",
    "استمرار": "Continue",
    "التالي": "Next",
    "السابق": "Previous",
    "موافقة": "Approve",
    "رفض": "Reject",
    "دفع": "Pay",
    "إزالة": "Remove",
    "إضافة": "Add",
    "نعم": "Yes",
    "لا": "No",
    "اختياري": "Optional",
    "مطلوب": "Required",
    "غير محدد": "Unspecified",
    "لا يوجد": "None",
    "جاري التحميل...": "Loading...",
    "جاري التحميل ...": "Loading...",
    "هذا الحقل مطلوب": "This field is required",
    "يرجى إدخال أحرف عربية فقط": "Please enter Arabic letters only",
    "لم يتم اختيار ملف": "No file selected",
    "الرجاء ادخال البيانات بشكل صحيح": "Please enter the data correctly",
    "هل أنت متأكد من حذف الطلب؟": "Are you sure you want to delete this request?",
    "حدث خطأ": "An error occurred",
    "خطأ في تحميل الأشخاص": "Error loading people",
    "خطأ في تحميل الفعاليات": "Error loading events",
    "لا يوجد نتائج": "No results",
    "لا توجد نتائج": "No results",
    "لا توجد بيانات": "No data",
    "لم يتم العثور على أي بيانات لعرضها.": "No data found to display.",
    "البحث...": "Search...",
    "ابحث عن ....": "Search...",
    "فلتر": "Filter",
    "مسح الكل": "Clear all",
    "— الكل —": "— All —",
    "الإجراءات": "Actions",
    "إجراءات": "Actions",
    "إلى": "to",
    "من": "of",
    "نتيجة": "results",
    # identity / person
    "الاسم": "Name",
    "الاسم الكامل": "Full name",
    "الاسم الأول": "First name",
    "الاسم الثاني": "Second name",
    "الاسم الثالث": "Third name",
    "الاسم الرابع": "Fourth name",
    "الشخص": "Person",
    "رقم الشخص": "Person ID",
    "الرقم القومي": "National ID",
    "تاريخ الميلاد": "Date of birth",
    "سنة الالتحاق": "Joining year",
    "فصيلة الدم": "Blood type",
    "اختر فصيلة الدم": "Choose blood type",
    "النوع": "Gender",
    "نوع الملتحق": "Applicant gender",
    "ذكر": "Male",
    "أنثى": "Female",
    "البريد الإلكتروني": "Email",
    "رقم الموبايل": "Mobile number",
    "الموبايل الشخصي": "Personal mobile",
    "موبايل الأب": "Father mobile",
    "موبايل الأم": "Mother mobile",
    "التليفون الأرضي": "Landline",
    "رقم الهاتف": "Phone number",
    "الهاتف": "Phone",
    "كود الشمندورة": "Shamandora code",
    "الكود": "Code",
    "الحالة": "Status",
    "قيد المراجعة": "Pending review",
    "مرفوض": "Rejected",
    "تمت الموافقة": "Approved",
    "التفاصيل": "Details",
    "ملاحظات": "Notes",
    "الملاحظة": "Note",
    "ملاحظة": "Note",
    "ملاحظة (اختياري)": "Note (optional)",
    "اكتب الملاحظة هنا": "Write the note here",
    "اكتب أي ملاحظة...": "Write any note...",
    "معلومات إضافية (اختياري)": "Additional info (optional)",
    "رسالة للإدارة": "Message to admin",
    # address / education
    "العنوان": "Address",
    "المنطقة": "Area",
    "الحي": "District",
    "رقم المبنى": "Building number",
    "رقم الدور": "Floor number",
    "رقم الشقة": "Apartment number",
    "الشارع الرئيسي": "Main street",
    "الشارع الفرعي": "Side street",
    "أقرب علامة مميزة": "Nearest landmark",
    "اسم المدرسة": "School name",
    "المدرسة": "School",
    "الكلية": "Faculty",
    "اسم الكلية": "Faculty name",
    "الجامعة": "University",
    "مكان العمل": "Workplace",
    "اسم الأب الروحي": "Spiritual father name",
    "الرتبة الكشفية": "Scout rank",
    "القطاع": "Sector",
    "القطاع الكشفي": "Scout sector",
    "اسم القطاع": "Sector name",
    "المرحلة": "Stage",
    "الموسم": "Season",
    "السنة": "Year",
    "التاريخ": "Date",
    "من تاريخ": "From date",
    "إلى تاريخ": "To date",
    "الصورة الشخصية": "Personal photo",
    "الصورة الكشفية": "Scout uniform photo",
    "الصور": "Photos",
    "البيانات الشخصية": "Personal information",
    "البيانات الأساسية": "Basic information",
    "الجزء الأول: البيانات الشخصية": "Part 1: Personal information",
    "الجزء الرابع: البيانات الكشفية": "Part 4: Scout information",
    "الجزء الأخير: الأسئلة المختصة بالقطاع": "Final part: Sector questions",
    "إجابة الملتحق": "Applicant answer",
    "نص السؤال": "Question text",
    # tables / enrolments
    "الطلب": "Request",
    "رقم الطلب": "Request number",
    "تاريخ التقديم": "Submitted at",
    "هل أكمل الأسئلة؟": "Completed questions?",
    "إدارة المستخدمين": "Manage users",
    "إدارة المستخدمين ": "Manage users",
    "الملتحقين الجدد": "New enrolments",
    "الالتحاقات الجديدة": "New enrolments",
    "قائمة الانتظار": "Waiting list",
    "مراجعة طلبات الالتحاق": "Review enrolment requests",
    "فورم التسجيل LIVE!": "LIVE registration form",
    "الحد الأقصى للطلبات": "Max request limits",
    "التحكم في أسئلة القطاعات": "Sector questions control",
    "احصائيات طلبات الالتحاق": "Enrolment analytics",
    "إكمال الأسئلة": "Complete questions",
    "نقل للتسجيل": "Move to enrolment",
    "فتح / إغلاق نموذج الالتحاق": "Open / close enrolment form",
    # dashboard
    "لوحه التحكم": "Dashboard",
    "لوحة التحكم": "Dashboard",
    "عدد المخدومين الحالي": "Current members count",
    "الفعاليات": "Events",
    "حضور المخدومين": "Member attendance",
    "طلبات حجز عهده": "Custody booking requests",
    "طلبات حجز الأماكن": "Place booking requests",
    "صفحتي الشخصية": "My profile",
    # finance / inventory / medicine
    "الكمية": "Quantity",
    "المبلغ": "Amount",
    "الإجمالي": "Total",
    "المتبقي": "Remaining",
    "الصنف": "Item",
    "الوحدة": "Unit",
    "وحدة": "Unit",
    "الموقع": "Location",
    "المكان": "Place",
    "الدواء": "Medicine",
    "مخزون الأدوية": "Medicine stock",
    "صرف دواء": "Dispense medicine",
    "اسم المجموعة": "Group name",
    "صلة القرابة": "Relationship",
    "الفئة العمرية": "Age group",
    "الشخص المرتبط": "Linked person",
    "بدون رقم": "No number",
    # forgot password / auth
    "نسيت كلمة المرور": "Forgot password",
    "استرجاع كلمة المرور": "Reset password",
    "أدخل رقم الهاتف": "Enter phone number",
    "أدخل رقم الهاتف": "Enter phone number",
    # misc selects
    "اختر الموسم": "Choose season",
    "اختر الفعالية": "Choose event",
    "اختر الموقع": "Choose location",
    "اختر المكان": "Choose place",
    "اختر المجموعة الكشفية": "Choose scout group",
    "اختر نوع الحدث أو المناسبة الكشفية": "Choose scout event type",
    "الفعالية": "Event",
    "بداية الفعالية:": "Event start:",
    "نهاية الفعالية:": "Event end:",
    "الفعالية:": "Event:",
    "الموسم:": "Season:",
    "الحالة:": "Status:",
    "الاسم:": "Name:",
    "الموبايل:": "Mobile:",
    "البيانات": "Data",
    "بيانات التحكم": "Control data",
    "صفحات التسجيل والدخول": "Login & registration pages",
    "كشافة الشمندورة - لوحة التحكم": "Shamandora Scout - Dashboard",
    "مجموعة الشمندورة الكشفية": "Shamandora Scout Group",
    "الشمندورة": "Shamandora",
    "طلب التحاق جديد": "New enrolment application",
    "انضم إلى أبطال البحر في مجموعة الشمندورة الكشفية": "Join the sea heroes in the Shamandora Scout Group",
    "بيانات ولي الأمر": "Guardian information",
    "البيانات الدراسية": "Educational information",
    "الأسئلة": "Questions",
    "المراجعة": "Review",
    "لا يوجد تسجيل حالياً": "Registration is closed",
    "التسجيل مغلق حالياً. تابعونا لمعرفة موعد فتح باب الالتحاق الجديد.": "Registration is currently closed. Follow us for the next enrolment opening.",
    "صورة شخصية (اختياري)": "Personal photo (optional)",
    "الحي السكني": "Residential district",
    "إضافة مستخدم": "Add user",
    "اضافة شخص جديد": "Add new person",
    "ادخال بيانات ملتحق جديد": "Enter new member data",
}

# Longer / multi-word phrases first when replacing
SORTED = sorted(MAP.items(), key=lambda kv: len(kv[0]), reverse=True)


def ensure_lang_files() -> None:
    en = json.loads(LANG_EN.read_text(encoding="utf-8"))
    ar = json.loads(LANG_AR.read_text(encoding="utf-8"))
    for ar_text, en_key in MAP.items():
        en.setdefault(en_key, en_key)
        ar.setdefault(en_key, ar_text)
    # Extra shared keys not from MAP values
    extras = {
        "Search...": ("Search...", "البحث..."),
        "Filter": ("Filter", "فلتر"),
        "Clear all": ("Clear all", "مسح الكل"),
        "— All —": ("— All —", "— الكل —"),
        "Actions": ("Actions", "الإجراءات"),
        "Showing": ("Showing", "عرض"),
        "to": ("to", "إلى"),
        "of": ("of", "من"),
        "results": ("results", "نتيجة"),
        "No data": ("No data", "لا توجد بيانات"),
        "No data found to display.": ("No data found to display.", "لم يتم العثور على أي بيانات لعرضها."),
        "Open / close enrolment form": ("Open / close enrolment form", "فتح / إغلاق نموذج الالتحاق"),
        "Dashboard": ("Dashboard", "لوحة التحكم"),
        "Submitted at": ("Submitted at", "تاريخ التقديم"),
        "Approve": ("Approve", "موافقة"),
        "Reject": ("Reject", "رفض"),
        "Complete questions": ("Complete questions", "إكمال الأسئلة"),
        "Move to enrolment": ("Move to enrolment", "نقل للتسجيل"),
        "Add user": ("Add user", "إضافة مستخدم"),
        "Manage users": ("Manage users", "إدارة المستخدمين"),
        "New enrolments": ("New enrolments", "الملتحقين الجدد"),
        "Waiting list": ("Waiting list", "قائمة الانتظار"),
        "Current members count": ("Current members count", "عدد المخدومين الحالي"),
        "Events": ("Events", "الفعاليات"),
        "Member attendance": ("Member attendance", "حضور المخدومين"),
        "Custody booking requests": ("Custody booking requests", "طلبات حجز عهده"),
        "Place booking requests": ("Place booking requests", "طلبات حجز الأماكن"),
        "My profile": ("My profile", "صفحتي الشخصية"),
        "Forgot password": ("Forgot password", "نسيت كلمة المرور"),
        "Reset password": ("Reset password", "استرجاع كلمة المرور"),
        "Enter phone number": ("Enter phone number", "أدخل رقم الهاتف"),
        "Phone number": ("Phone number", "رقم الهاتف"),
        "Send reset link": ("Send reset link", "إرسال رابط الاسترجاع"),
        "Back to login": ("Back to login", "العودة لتسجيل الدخول"),
        "New enrolment application": ("New enrolment application", "طلب التحاق جديد"),
        "Join the sea heroes in the Shamandora Scout Group": (
            "Join the sea heroes in the Shamandora Scout Group",
            "انضم إلى أبطال البحر في مجموعة الشمندورة الكشفية",
        ),
        "Personal information": ("Personal information", "البيانات الشخصية"),
        "Guardian information": ("Guardian information", "بيانات ولي الأمر"),
        "Educational information": ("Educational information", "البيانات الدراسية"),
        "Questions": ("Questions", "الأسئلة"),
        "Review": ("Review", "المراجعة"),
        "Registration is closed": ("Registration is closed", "لا يوجد تسجيل حالياً"),
        "Sea Shamandora Scout Group — Alexandria": (
            "Sea Shamandora Scout Group — Alexandria",
            "مجموعة الشمندورة الكشفية البحرية — الإسكندرية",
        ),
        "Required fields are marked": ("Required fields are marked", "الحقول المطلوبة عليها علامة"),
        "All": ("All", "الكل"),
        "None": ("None", "لا يوجد"),
        "Yes": ("Yes", "نعم"),
        "No": ("No", "لا"),
        "Male": ("Male", "ذكر"),
        "Female": ("Female", "أنثى"),
        "Optional": ("Optional", "اختياري"),
        "Continue": ("Continue", "استمرار"),
        "Next": ("Next", "التالي"),
        "Previous": ("Previous", "السابق"),
        "Save": ("Save", "حفظ"),
        "Cancel": ("Cancel", "إلغاء"),
        "Back": ("Back", "رجوع"),
        "Edit": ("Edit", "تعديل"),
        "Delete": ("Delete", "حذف"),
        "View": ("View", "عرض"),
        "Name": ("Name", "الاسم"),
        "Full name": ("Full name", "الاسم الكامل"),
        "First name": ("First name", "الاسم الأول"),
        "Second name": ("Second name", "الاسم الثاني"),
        "Third name": ("Third name", "الاسم الثالث"),
        "Fourth name": ("Fourth name", "الاسم الرابع"),
        "National ID": ("National ID", "الرقم القومي"),
        "Date of birth": ("Date of birth", "تاريخ الميلاد"),
        "Blood type": ("Blood type", "فصيلة الدم"),
        "Mobile number": ("Mobile number", "رقم الموبايل"),
        "Status": ("Status", "الحالة"),
        "Sector": ("Sector", "القطاع"),
        "Stage": ("Stage", "المرحلة"),
        "Request": ("Request", "الطلب"),
        "Completed questions?": ("Completed questions?", "هل أكمل الأسئلة؟"),
        "This field is required": ("This field is required", "هذا الحقل مطلوب"),
    }
    for key, (en_v, ar_v) in extras.items():
        en.setdefault(key, en_v)
        ar.setdefault(key, ar_v)

    LANG_EN.write_text(json.dumps(en, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")
    LANG_AR.write_text(json.dumps(ar, ensure_ascii=False, indent=4) + "\n", encoding="utf-8")


def wrap_php_string(ar: str, en: str, text: str) -> str:
    """Replace Arabic literals in PHP/Blade array and attribute contexts."""
    patterns = [
        # 'label' => 'عربي'
        (
            re.compile(r"(['\"](?:label|disabledLabel|title|name|placeholder)['\"]\s*=>\s*)(['\"])" + re.escape(ar) + r"\2"),
            rf"\1__('{en}')",
        ),
        # title="عربي" / placeholder="عربي" / alt="عربي"
        (
            re.compile(r"((?:title|placeholder|alt|aria-label)=)(['\"])" + re.escape(ar) + r"\2"),
            rf"\1\"{{{{ __('{en}') }}}}\"",
        ),
        # pageTitle' => 'عربي'
        (
            re.compile(r"(pageTitle['\"]?\s*=>\s*)(['\"])" + re.escape(ar) + r"\2"),
            rf"\1__('{en}')",
        ),
        # >عربي< plain text nodes (conservative: exact)
        (
            re.compile(r">\s*" + re.escape(ar) + r"\s*<"),
            f">{{{{ __('{en}') }}}}<",
        ),
    ]
    for rx, repl in patterns:
        text = rx.sub(repl, text)
    return text


def process_file(path: Path) -> bool:
    original = path.read_text(encoding="utf-8")
    text = original
    for ar, en in SORTED:
        text = wrap_php_string(ar, en, text)
    if text != original:
        path.write_text(text, encoding="utf-8")
        return True
    return False


def main() -> None:
    ensure_lang_files()
    changed = []
    for path in sorted(VIEWS.rglob("*.blade.php")):
        # Skip vendor-like noise if any
        if process_file(path):
            changed.append(str(path.relative_to(ROOT)))
    print(f"Updated {len(changed)} files")
    for c in changed[:80]:
        print(" -", c)
    if len(changed) > 80:
        print(f" ... and {len(changed) - 80} more")


if __name__ == "__main__":
    main()
