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

        .top {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
            justify-content: space-between;
        }

        .left {
            display: flex;
            align-items: center;
            gap: 18px;
            flex-wrap: wrap;
        }

        .avatarWrap {
            position: relative;
            width: 140px;
            height: 140px;
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

        .name {
            margin: 0;
            font-size: 22px;
        }

        .muted {
            margin: 4px 0 0;
            color: #666;
            font-size: 14px;
        }

        .btnx {
            display: inline-block;
            padding: 10px 14px;
            border-radius: 10px;
            background: #0d6efd;
            color: #fff;
            text-decoration: none;
            font-weight: 600;
        }

        .btnx:hover {
            background: #0b5ed7;
            color: #fff;
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

        .field {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 12px 14px;
            background: #fafafa;
        }

        .label {
            font-size: 12px;
            color: #666;
            margin-bottom: 6px;
        }

        .value {
            font-size: 15px;
            font-weight: 600;
            color: #111;
        }

        .success {
            background: #e9f7ef;
            border: 1px solid #b7ebc6;
            color: #1e6b3a;
            padding: 12px 14px;
            border-radius: 12px;
            margin-bottom: 14px;
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

        @if (session('success'))
            <div class="success">{{ session('success') }}</div>
        @endif

        <div class="top">
            <div class="left">
                <div class="avatarWrap">
                    @if ($hasPhoto)
                        <img class="avatarImg" src="{{ $photoUrl }}" alt="الصورة الشخصية">
                    @else
                        <div class="fbAvatar">{{ $initials ?: 'م' }}</div>
                    @endif
                </div>

                <div>
                    <h2 class="name">
                        {{ trim(($user->FirstName ?? '') . ' ' . ($user->SecondName ?? '') . ' ' . ($user->ThirdName ?? '') . ' ' . ($user->FourthName ?? '')) ?: 'المستخدم' }}
                    </h2>
                    <p class="muted">معلومات الملف الشخصي</p>
                </div>
            </div>

            <div>
                <a class="btnx" href="{{ route('profile.edit') }}">تعديل الملف الشخصي</a>
            </div>
        </div>

        <div class="grid">
            <div class="field">
                <div class="label">الاسم الأول</div>
                <div class="value">{{ $user->FirstName }}</div>
            </div>
            <div class="field">
                <div class="label">الاسم الثاني</div>
                <div class="value">{{ $user->SecondName }}</div>
            </div>
            <div class="field">
                <div class="label">الاسم الثالث</div>
                <div class="value">{{ $user->ThirdName }}</div>
            </div>
            <div class="field">
                <div class="label">الاسم الرابع</div>
                <div class="value">{{ $user->FourthName }}</div>
            </div>
            <div class="field">
                <div class="label">سنة الانضمام للكشافة</div>
                <div class="value">{{ $user->ScoutJoiningYear }}</div>
            </div>
            <div class="field">
                <div class="label">رقم الموبايل</div>
                <div class="value">{{ $phone ?? '-' }}</div>
            </div>
        </div>

    </div>
@endsection
