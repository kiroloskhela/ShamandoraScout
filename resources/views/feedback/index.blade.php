<!DOCTYPE html>
@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $dir = $isRtl ? 'rtl' : 'ltr';
@endphp
<html lang="{{ $locale }}" dir="{{ $dir }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('partials.seo-head', [
        'seoTitle' => __('Feedback | Shamandora Scout'),
        'seoDescription' => __('Send feedback to Shamandora Scout — official Egyptian Sea Scout group (الشمندوره البحريه).'),
    ])
    <link rel="icon" type="image/webp" href={{ asset('img/shamandora.webp') }}>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&family=Source+Sans+3:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
    :root {
        --primary-color: #333;
        --secondary-color: #555;
        --accent-color: #007bff;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --light-gray: #f8f9fa;
        --medium-gray: #e9ecef;
        --dark-gray: #6c757d;
        --border-color: #dee2e6;
        --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        --border-radius: 8px;
        --transition: all 0.3s ease;
    }

    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: {{ $locale === 'ar' ? "'Cairo'" : "'Source Sans 3', system-ui, sans-serif" }};
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        min-height: 100vh;
        padding: 20px;
        color: var(--primary-color);
        line-height: 1.6;
    }

    .container {
        max-width: 900px;
        margin: 0 auto;
        background: white;
        border-radius: var(--border-radius);
        box-shadow: var(--shadow);
        overflow: hidden;
    }

    /* Header Section */
    .header {
        background: linear-gradient(135deg, var(--light-gray) 0%, #ffffff 100%);
        padding: 40px 30px;
        text-align: center;
        border-bottom: 1px solid var(--border-color);
    }

    .header__logo {
        max-width: 100px;
        margin-bottom: 20px;
        border-radius: 50%;
    }

    .header__title {
        font-size: 2.5rem;
        margin-bottom: 15px;
        color: var(--primary-color);
        font-weight: 700;

    }

    .header__subtitle {
        font-size: 1.2rem;
        color: var(--secondary-color);
        margin-bottom: 20px;
    }

    .header__description {
        font-size: 1.1rem;
        color: var(--dark-gray);
        margin-bottom: 30px;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
    }

    .info-section {
        background: rgba(0, 123, 255, 0.05);
        border: 1px solid rgba(0, 123, 255, 0.1);
        border-radius: var(--border-radius);
        padding: 25px;
        margin-bottom: 20px;
        text-align: right;
    }

    .info-section__title {
        font-size: 1.3rem;
        font-weight: 600;
        color: var(--primary-color);
        margin-bottom: 15px;
        display: flex;
        align-items: center;

    }

    .info-section__icon {
        margin-left: 10px;
        font-size: 1.5rem;
    }

    .info-section__list {
        list-style: none;
        padding: 0;
    }

    .info-section__item {
        margin-bottom: 8px;
        padding-right: 20px;
        position: relative;
    }

    .info-section__item::before {
        content: "•";
        position: absolute;
        right: 0;
        color: var(--accent-color);
        font-weight: bold;
    }

    .notice {
        background: rgba(255, 193, 7, 0.1);
        border: 1px solid rgba(255, 193, 7, 0.3);
        border-radius: var(--border-radius);
        padding: 20px;
        margin-top: 20px;
        font-weight: 600;
        color: var(--primary-color);
    }

    /* Form Section */
    .form {
        padding: 40px 30px;
    }

    .form-group {
        margin-bottom: 25px;
    }

    .form-group--inline {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: var(--primary-color);
        font-size: 1.1rem;
    }

    .label--required::after {
        content: " *";
        color: var(--danger-color);
    }

    .input,
    .textarea,
    .select {
        width: 100%;
        padding: 12px 16px;
        padding-inline-end: 2.5rem;
        border: 2px solid var(--border-color);
        border-radius: var(--border-radius);
        font-size: 1rem;
        font-family: inherit;
        transition: var(--transition);
        background-color: white;
        -webkit-appearance: none;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2364748b'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-size: 1rem 1rem;
        background-position: right 0.75rem center;
    }

    html[dir="rtl"] .select {
        background-position: left 0.75rem center;
    }

    .input:focus,
    .textarea:focus,
    .select:focus {
        outline: none;
        border-color: var(--accent-color);
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.1);
    }

    .textarea {
        resize: vertical;
        min-height: 100px;
    }

    .select {
        cursor: pointer;
    }

    /* Section Styles */
    .section {
        margin-bottom: 40px;
        border: 1px solid var(--border-color);
        border-radius: var(--border-radius);
        padding: 30px;
        background: var(--light-gray);
        transition: var(--transition);
    }

    .section:hover {
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
    }

    .section__title {
        font-size: 1.8rem;
        color: var(--primary-color);
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--border-color);
        display: flex;
        align-items: center;

    }

    .section__icon {
        margin-left: 12px;
        font-size: 1.5rem;
    }

    .section__description {
        color: var(--dark-gray);
        margin-bottom: 25px;
        font-size: 1.05rem;
        line-height: 1.6;
    }



    /* Rating System */
    .rating-stars {
        unicode-bidi: bidi-override;
        display: inline-block;
        font-size: 2rem;
        user-select: none;
    }

    .rating-stars input[type="radio"] {
        display: none;
    }

    .rating-stars label {
        color: var(--medium-gray);
        cursor: pointer;
        transition: var(--transition);
    }

    .rating-stars input:checked~label,
    .rating-stars label:hover,
    .rating-stars label:hover~label {
        color: #ffc107;
    }

    .rating__input {
        display: none;
    }

    .rating__label {
        font-size: 2rem;
        color: var(--medium-gray);
        cursor: pointer;
        transition: var(--transition);
        padding: 5px;
    }

    .rating__label:hover,
    .rating_label:hover~.rating_label,
    .rating_input:checked~.rating_label {
        color: #ffd700;
        transform: scale(1.1);
    }

    .rating__value {
        text-align: center;
        margin-top: 10px;
        font-weight: 600;
        color: var(--primary-color);
    }

    /* Submit Section */
    .submit-section {
        padding: 40px 30px;
        background: var(--light-gray);
        border-top: 1px solid var(--border-color);
        text-align: center;
    }

    .btn {
        padding: 15px 30px;
        border: none;
        border-radius: var(--border-radius);
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        display: inline-block;
        margin: 0 10px;
    }

    .btn--primary {
        background: var(--primary-color);
        color: white;
    }

    .btn--primary:hover {
        background: var(--secondary-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    .btn--secondary {
        background: var(--accent-color);
        color: white;
    }

    .btn--secondary:hover {
        background: #0056b3;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 123, 255, 0.3);
    }

    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        justify-content: center;
        align-items: center;
    }

    .modal--visible {
        display: flex;
    }

    .modal__content {
        background: white;
        padding: 40px;
        border-radius: var(--border-radius);
        text-align: center;
        max-width: 500px;
        width: 90%;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
        animation: modalAppear 0.3s ease;
    }

    @keyframes modalAppear {
        from {
            opacity: 0;
            transform: scale(0.8);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .modal__icon {
        font-size: 4rem;
        margin-bottom: 20px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal__title {
        font-size: 1.8rem;
        margin-bottom: 15px;
        color: var(--primary-color);
    }

    .modal__text {
        color: var(--dark-gray);
        margin-bottom: 30px;
        line-height: 1.6;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        body {
            padding: 10px;
        }

        .container {
            margin: 0;
        }

        .header,
        .form,
        .submit-section {
            padding: 20px;
        }

        .header__title {
            font-size: 2rem;
        }

        .form-group--inline {
            grid-template-columns: 1fr;
        }

        .section {
            padding: 20px;
        }

        .section__title {
            font-size: 1.5rem;
        }

        .rating__label {
            font-size: 1.5rem;
        }

        .modal__content {
            padding: 30px 20px;
        }

        .btn {
            display: block;
            margin: 10px auto;
            width: 100%;
        }
    }

    @media (max-width: 480px) {
        .header__title {
            font-size: 1.7rem;
        }

        .info-section,
        .section {
            padding: 15px;
        }

        .rating {
            gap: 2px;
        }

        .rating__label {
            font-size: 1.3rem;
            padding: 3px;
        }
    }
    </style>
</head>

<body>
    <div class="container">
        <!-- Header Section -->
        <header class="header">
            <img src={{ asset('img/shamandora.webp') }} alt="{{ __('Camp logo') }}" class="header__logo">
            <h1 class="header__title">{{ __('Combined camp evaluation 2025 - Keffi') }}</h1>
            <p class="header__subtitle">{{ __('May God reward you for your effort and service during the camp') }}</p>
            <p class="header__description">
                {{ __('Feedback intro paragraph') }}
            </p>

            <div class="info-section">
                <h3 class="info-section__title">
                    <span class="info-section__icon">🎯</span>
                    {{ __('Evaluation purpose') }}
                </h3>
                <ul class="info-section__list">
                    <li class="info-section__item">{{ __('Learn from this experience and build on it') }}</li>
                    <li class="info-section__item">{{ __('Avoid any shortcomings during the camp') }}</li>
                    <li class="info-section__item">{{ __('Record important points for future reference') }}</li>
                    <li class="info-section__item">{{ __('Save time when we review and plan together') }}</li>
                </ul>
            </div>

            <div class="info-section">
                <h3 class="info-section__title">
                    <span class="info-section__icon">📝</span>
                    {{ __('Special request') }}
                </h3>
                <ul class="info-section__list">
                    <li class="info-section__item">{{ __('Write your opinion objectively') }}</li>
                    <li class="info-section__item">{{ __('Be honest and constructive in your notes') }}</li>
                    <li class="info-section__item">{{ __('The goal is improvement, not blame') }}</li>
                </ul>
            </div>

            <p class="header__description">
                {{ __('Thank you from the heart — your presence made a difference ❤') }}<br>
                {{ __('Next camp will be stronger and better, God willing') }}
            </p>

            <div class="notice">
                {{ __('Most fields in this form are optional. Fill what you see fit and submit anytime.') }}
            </div>
        </header>

        <!-- Form Section -->
        <form id="evaluationForm" class="form" action="/feedback" method="POST">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <!-- Personal Information -->
            <div class="form-group">
                <label for="participantName" class="label">{{ __('Full Arabic name (three parts)') }}</label>
                <input type="text" id="participantName" name="participant_name" class="input"
                    placeholder="{{ __('Enter your full name') }}">
            </div>

            <div class="form-group form-group--inline">
                <div>
                    <label for="mainTeam" class="label">{{ __('Main team') }}</label>
                    <select id="mainTeam" name="main_team" class="select">
                        <option value="">{{ __('Choose main team') }}</option>
                        <option value="braem">{{ __('Cubs (Baraem)') }}</option>
                        <option value="ashbal">{{ __('Cubs (Ashbal)') }}</option>
                        <option value="zahrat">{{ __('Flowers (Zahrat)') }}</option>
                        <option value="kashafa">{{ __('Scouts (Kashafa)') }}</option>
                        <option value="morshedat">{{ __('Guides (Morshedat)') }}</option>
                        <option value="motakadem">{{ __('Advanced (Motakadem)') }}</option>
                        <option value="raedat">{{ __('Pioneers (Raedat)') }}</option>
                        <option value="jawala">{{ __('Rovers (Jawala)') }}</option>
                        <option value="edareat">{{ __('Administrative (Edareat)') }}</option>
                    </select>
                </div>
                <div>
                    <label for="subTeam" class="label">{{ __('Sub team') }}</label>
                    <select id="subTeam" name="sub_team" class="select">
                        <option value="">{{ __('Choose sub team') }}</option>
                        <option value="media">{{ __('Media team') }}</option>
                        <option value="ohda">{{ __('Custody team') }}</option>
                        <option value="esafate">{{ __('First aid team') }}</option>
                        <option value="secretary">{{ __('Secretary team') }}</option>
                        <option value="moshtaryat">{{ __('Purchasing team') }}</option>
                        <option value="malia">{{ __('Finance team') }}</option>
                        <option value="matbakh">{{ __('Kitchen team') }}</option>
                        <option value="tawselhadaf">{{ __('Goal delivery team') }}</option>
                        <option value="bernameg">{{ __('Program team') }}</option>
                    </select>
                </div>
            </div>

            <!-- Program Section -->
            <div class="section">

                <h2 class="section__title">

                    {{ __('General program') }}
                </h2>
                <p class="section__description">
                    {{ __('General program section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="program{{ $i }}" name="program_rating" value="{{ $i }}" required>
                        <label for="program{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="programPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="programPros" name="program_pros" class="textarea"
                        placeholder="{{ __('Mention program positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="programCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="programCons" name="program_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>


            <!-- Leaders Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Leader distribution') }}
                </h2>
                <p class="section__description">
                    {{ __('Leader distribution section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="leaders{{ $i }}" name="leaders_rating" value="{{ $i }}" required>
                        <label for="leaders{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="leadersPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="leadersPros" name="leaders_pros" class="textarea"
                        placeholder="{{ __('Mention leader distribution positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="leadersCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="leadersCons" name="leaders_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>


            <!-- Games Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Games') }}
                </h2>
                <p class="section__description">
                    {{ __('Games section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="games{{ $i }}" name="games_rating" value="{{ $i }}" required>
                        <label for="games{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="gamesPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="gamesPros" name="games_pros" class="textarea"
                        placeholder="{{ __('Mention games positives... (alt)') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="gamesCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="gamesCons" name="games_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>


            <!-- goal_delivery Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Goal delivery') }}
                </h2>
                <p class="section__description">
                    {{ __('Goal delivery section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="goal_delivery{{ $i }}" name="goal_delivery_rating" value="{{ $i }}"
                            required>
                        <label for="goal_delivery{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="goal_deliveryPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="goal_deliveryPros" name="goal_delivery_pros" class="textarea"
                        placeholder="{{ __('Mention goal delivery positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="goal_deliveryCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="goal_deliveryCons" name="goal_delivery_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>


            <!-- logo Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Camp anthem') }}
                </h2>
                <p class="section__description">
                    {{ __('Camp anthem section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="logo{{ $i }}" name="logo_rating" value="{{ $i }}" required>
                        <label for="logo{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="logoPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="logoPros" name="logo_pros" class="textarea"
                        placeholder="{{ __('Mention anthem positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="logoCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="logoCons" name="logo_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- gift Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Gifts') }}
                </h2>
                <p class="section__description">
                    {{ __('Gifts section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="gift{{ $i }}" name="gift_rating" value="{{ $i }}" required>
                        <label for="gift{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="giftPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="giftPros" name="gift_pros" class="textarea"
                        placeholder="{{ __('Mention gifts positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="giftCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="giftCons" name="gift_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- secretary Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Secretariat') }}
                </h2>
                <p class="section__description">
                    {{ __('Secretariat section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="secretary{{ $i }}" name="secretary_rating" value="{{ $i }}" required>
                        <label for="secretary{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="secretaryPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="secretaryPros" name="secretary_pros" class="textarea"
                        placeholder="{{ __('Mention secretariat positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="secretaryCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="secretaryCons" name="secretary_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- media Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Media') }}
                </h2>
                <p class="section__description">
                    {{ __('Media section description (feedback)') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="media{{ $i }}" name="media_rating" value="{{ $i }}" required>
                        <label for="media{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="mediaPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="mediaPros" name="media_pros" class="textarea"
                        placeholder="{{ __('Mention media positives... (feedback)') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="mediaCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="mediaCons" name="media_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- emergency Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('First aid') }}
                </h2>
                <p class="section__description">
                    {{ __('First aid section description (feedback)') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="emergency{{ $i }}" name="emergency_rating" value="{{ $i }}" required>
                        <label for="emergency{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="emergencyPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="emergencyPros" name="emergency_pros" class="textarea"
                        placeholder="{{ __('Mention first aid positives... (feedback)') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="emergencyCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="emergencyCons" name="emergency_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- kitchen Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Kitchen') }}
                </h2>
                <p class="section__description">
                    {{ __('Kitchen section description (feedback)') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="kitchen{{ $i }}" name="kitchen_rating" value="{{ $i }}" required>
                        <label for="kitchen{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="kitchenPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="kitchenPros" name="kitchen_pros" class="textarea"
                        placeholder="{{ __('Mention kitchen positives... (feedback)') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="kitchenCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="kitchenCons" name="kitchen_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- finance Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Finance') }}
                </h2>
                <p class="section__description">
                    {{ __('Finance section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="finance{{ $i }}" name="finance_rating" value="{{ $i }}" required>
                        <label for="finance{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="financePros" class="label">{{ __('Pros') }}</label>
                    <textarea id="financePros" name="finance_pros" class="textarea"
                        placeholder="{{ __('Mention finance positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="financeCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="financeCons" name="finance_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- custody Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Custody/inventory') }}
                </h2>
                <p class="section__description">
                    {{ __('Custody section description (feedback)') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="custody{{ $i }}" name="custody_rating" value="{{ $i }}" required>
                        <label for="custody{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="custodyPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="custodyPros" name="custody_pros" class="textarea"
                        placeholder="{{ __('Mention custody positives... (feedback)') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="custodyCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="custodyCons" name="custody_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- purchase Section -->
            <div class="section">
                <h2 class="section__title">

                    {{ __('Purchasing') }}
                </h2>
                <p class="section__description">
                    {{ __('Purchasing section description') }}
                </p>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="purchase{{ $i }}" name="purchase_rating" value="{{ $i }}" required>
                        <label for="purchase{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="purchasePros" class="label">{{ __('Pros') }}</label>
                    <textarea id="purchasePros" name="purchase_pros" class="textarea"
                        placeholder="{{ __('Mention purchasing positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="purchaseCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="purchaseCons" name="purchase_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- transportation Section -->
            <div class="section">
                <h2 class="section__title">
                    <span class="section__icon"></span>
                    {{ __('Transportation') }}
                </h2>

                <div class="form-group">
                    <label class="label label--required">{{ __('Overall rating (1-10)') }}</label>
                    <div class="rating-stars">
                        @for ($i = 10; $i >= 1; $i--)
                        <input type="radio" id="transportation{{ $i }}" name="transportation_rating" value="{{ $i }}"
                            required>
                        <label for="transportation{{ $i }}">★</label>
                        @endfor
                    </div>
                </div>

                <div class="form-group">
                    <label for="transportationPros" class="label">{{ __('Pros') }}</label>
                    <textarea id="transportationPros" name="transportation_pros" class="textarea"
                        placeholder="{{ __('Mention transportation positives...') }}"></textarea>
                </div>

                <div class="form-group">
                    <label for="transportationCons" class="label">{{ __('Cons and suggested improvements') }}</label>
                    <textarea id="transportationCons" name="transportation_cons" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <div class="section">
                <div class="form-group">
                    <label for="generalSuggestions" class="label">{{ __('Do you have any general suggestions? Please write them here') }}</label>
                    <textarea id="generalSuggestions" name="general_suggestions" class="textarea"
                        placeholder="{{ __('Mention improvements and suggestions...') }}"></textarea>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="submit-section">
                <button type="submit" class="btn btn--primary">{{ __('Submit evaluation') }}</button>
            </div>
        </form>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="modal">
        <div class="modal__content">
            <div class="modal__icon">
                <img src="img/shamandora.webp" alt="Success Icon" style="max-width: 100px; height: auto;">
            </div>
            <h2 class="modal__title" id="modalTitle">{{ __('Submitted successfully!') }}</h2>
            <p class="modal__text" id="modalText">{{ __('Thank you for your time and honest feedback. Your evaluation will help us improve future camps.') }}</p>
            <button class="btn btn--primary" onclick="closeModal()">{{ __('Close') }}</button>
        </div>
    </div>


    <script>
    // Rating system functionality
    document.querySelectorAll('.rating__input').forEach(input => {
        input.addEventListener('change', function() {
            const value = this.value;
            const valueDisplay = this.closest('.form-group').querySelector('.rating__value');
            if (valueDisplay) {
                valueDisplay.textContent = `${value}`;

            }
        });
    });

    // Form submission
    document.getElementById('evaluationForm').addEventListener('submit', async function(e) {
        e.preventDefault();
        const form = this;
        const submitBtn = form.querySelector('.btn--primary');
        const originalText = submitBtn.textContent;
        submitBtn.textContent = @json(__('Submitting...'));
        submitBtn.disabled = true;

        const formData = new FormData(form);
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                body: formData
            });

            if (response.ok) {
                showModal();
                form.reset();
            } else {
                alert(@json(__('An error occurred while submitting. Please try again.')));
            }
        } catch (error) {
            alert(@json(__('Failed to connect to the server.')));
        }

        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });


    // Modal functions
    function showModal() {
        // Get the participant name from the form
        const participantName = document.getElementById('participantName').value.trim();
        const modalTitle = document.getElementById('modalTitle');
        const modalText = document.getElementById('modalText');

        if (participantName) {
            modalTitle.textContent = @json(__('Thank you :name ❤️')).replace(':name', participantName);
            modalText.textContent = @json(__('Your feedback was submitted successfully! Thank you for your time and honest feedback. Your evaluation will help us improve future camps.'));
        } else {
            modalTitle.textContent = @json(__('Thank you ❤️'));
            modalText.textContent = @json(__('Your feedback was submitted successfully! Thank you for your time and honest feedback. Your evaluation will help us improve future camps.'));
        }

        document.getElementById('successModal').classList.add('modal--visible');
    }

    function closeModal() {
        document.getElementById('successModal').classList.remove('modal--visible');
    }

    // Close modal on outside click
    document.getElementById('successModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });

    // Keyboard navigation for modal
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeModal();
        }
    });
    </script>
</body>

</html>