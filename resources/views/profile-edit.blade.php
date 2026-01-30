@extends('layouts.app')

@section('content')
    <style>
        .cardx {
            max-width: 900px;
            margin: 24px auto;
            background: #fff;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, .06);
        }

        .sectionTitle {
            margin: 0 0 14px;
            font-size: 18px;
        }

        .rowTop {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .avatarWrap {
            position: relative;
            width: 140px;
            height: 140px;
            flex: 0 0 auto;
        }

        .avatarImg {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ddd;
            background: #f5f5f5;
        }

        .fbAvatar {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 40px;
            color: #fff;
            border: 3px solid #ddd;
            background: linear-gradient(135deg, #1877f2, #42b72a);
            user-select: none;
        }

        .editBtn {
            position: absolute;
            bottom: 6px;
            right: 6px;
            width: 38px;
            height: 38px;
            border-radius: 50%;
            border: 2px solid #fff;
            background: #0d6efd;
            color: #fff;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0, 0, 0, .25);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .editBtn:hover {
            background: #0b5ed7;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: 18px;
        }

        @media(max-width:768px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        .form-group label {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 6px;
            display: block;
        }

        .form-control {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
            transition: .15s;
        }

        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, .12);
        }

        .actions {
            display: flex;
            gap: 10px;
            justify-content: flex-start;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .btnPrimary {
            background: #0d6efd;
            border: none;
            color: #fff;
            padding: 10px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
        }

        .btnPrimary:hover {
            background: #0b5ed7;
        }

        .btnLight {
            background: #f2f2f2;
            border: none;
            color: #111;
            padding: 10px 16px;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 700;
            text-decoration: none;
            display: inline-block;
        }

        .btnLight:hover {
            background: #e8e8e8;
            color: #111;
        }

        .error {
            color: #d93025;
            font-size: 13px;
            margin-top: 6px;
        }

        .divider {
            height: 1px;
            background: #eee;
            margin: 22px 0;
        }

        .hint {
            margin: 0;
            color: #666;
            font-size: 14px;
        }
    </style>

    @php
        $hasPhoto = $personImage && !empty($personImage->PersonSystemImagePath);
        $photoUrl = $hasPhoto ? asset('storage/' . $personImage->PersonSystemImagePath) : null;

        $first = $user->FirstName ?? '';
        $second = $user->SecondName ?? '';
        $initials = strtoupper(mb_substr($first, 0, 1) . mb_substr($second, 0, 1));
    @endphp

    <div class="cardx" dir="rtl">

        <h3 class="sectionTitle">تعديل الملف الشخصي</h3>

        {{-- تحديث البيانات + الصورة --}}
        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" id="profileForm">
            @csrf
            @method('PUT')

            <div class="rowTop">
                <div class="avatarWrap">
                    @if ($hasPhoto)
                        <img class="avatarImg" src="{{ $photoUrl }}" alt="الصورة الشخصية">
                    @else
                        <div class="fbAvatar">{{ $initials ?: 'م' }}</div>
                    @endif

                    <input type="file" name="profile_image" id="profile_image" accept="image/*" hidden>

                    <button type="button" class="editBtn" onclick="document.getElementById('profile_image').click();"
                        title="تغيير الصورة">
                        📷
                    </button>
                </div>

                <div>
                    <p class="hint">اضغط على زر الكاميرا لتغيير الصورة الشخصية.</p>
                    @error('profile_image')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="grid">
                <div class="form-group">
                    <label for="FirstName">الاسم الأول</label>
                    <input class="form-control" id="FirstName" name="FirstName"
                        value="{{ old('FirstName', $user->FirstName ?? '') }}">
                    @error('FirstName')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="SecondName">الاسم الثاني</label>
                    <input class="form-control" id="SecondName" name="SecondName"
                        value="{{ old('SecondName', $user->SecondName ?? '') }}">
                    @error('SecondName')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="ThirdName">الاسم الثالث</label>
                    <input class="form-control" id="ThirdName" name="ThirdName"
                        value="{{ old('ThirdName', $user->ThirdName ?? '') }}">
                    @error('ThirdName')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="FourthName">الاسم الرابع</label>
                    <input class="form-control" id="FourthName" name="FourthName"
                        value="{{ old('FourthName', $user->FourthName ?? '') }}">
                    @error('FourthName')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="ScoutJoiningYear">سنة الانضمام للكشافة</label>
                    <input class="form-control" type="number" id="ScoutJoiningYear" name="ScoutJoiningYear"
                        value="{{ old('ScoutJoiningYear', $user->ScoutJoiningYear ?? '') }}">
                    @error('ScoutJoiningYear')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="PersonPersonalMobileNumber">رقم الموبايل</label>
                    <input class="form-control" id="PersonPersonalMobileNumber" name="PersonPersonalMobileNumber"
                        value="{{ old('PersonPersonalMobileNumber', $phone ?? '') }}">
                    @error('PersonPersonalMobileNumber')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="actions">
                <a class="btnLight" href="{{ route('profile.show') }}">رجوع</a>
                <button class="btnPrimary" type="submit">حفظ التعديلات</button>
            </div>
        </form>

        <div class="divider"></div>

        {{-- تغيير كلمة المرور --}}
        <h3 class="sectionTitle">تغيير كلمة المرور</h3>

        <form method="POST" action="{{ route('profile.password.update') }}">
            @csrf
            @method('PUT')

            <div class="grid">
                <div class="form-group">
                    <label for="password">كلمة مرور جديدة</label>
                    <input class="form-control" type="password" name="password" id="password">
                    @error('password')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password_confirmation">تأكيد كلمة المرور</label>
                    <input class="form-control" type="password" name="password_confirmation" id="password_confirmation">
                </div>
            </div>

            <div class="actions">
                <button class="btnPrimary" type="submit">تحديث كلمة المرور</button>
            </div>
        </form>

    </div>

    <script>
        // إرسال الفورم تلقائياً عند اختيار صورة جديدة
        document.getElementById('profile_image')?.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                document.getElementById('profileForm').submit();
            }
        });
    </script>
@endsection
