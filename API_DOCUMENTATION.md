# AthanasiusScouts2024 API Documentation

## Overview
This document describes the available API endpoints, their usage, expected input, and output examples for the AthanasiusScouts2024 project.

---

## Authentication
### POST `/api/login`
**Description:** Authenticate a user and retrieve an access token.
**Request Body Example:**
```json
{
  "email": "user@example.com",
  "password": "yourpassword"
}
```
**Response Example:**
```json
{
  "token": "...",
  "user": {
    "id": 1,
    "name": "John Doe"
  }
}
```

---

## Get Persons for a User
### GET `/api/show-persons`
**Description:** Returns a list of persons filtered by group membership for the **authenticated** user. The user is identified solely from the bearer token (`Authorization: Bearer {token}`); any client-supplied `id` is ignored and cannot be used to view another user's data.
**Response Example:**
```json
{
  "persons": [
    {
      "PersonID": 1,
      "ShamandoraCode": "SH-00001",
      "FirstName": "John",
      "SecondName": "Doe",
      "ThirdName": "Smith",
      "FourthName": "Brown",
      "QetaaName": "Group Name",
      "ScoutJoiningYear": 2020,
      "SanaMarhalaName": "Marhala Name",
      "RaqamQawmy": "12345678901234",
      "PersonPersonalMobileNumber": "01234567890",
      "QetaaID": 2,
      "GroupPersonID": 1,
      "HasAnsweredQuestions": "نعم",
      "SanaMarhalaID": 3,
      "full_name": "John Doe Smith Brown"
    },
    // ...more persons
  ]
}
```

---

## Get Person Profile
### GET `/api/person/{id}`
**Description:** Returns all joined data for the **authenticated** user's own profile. The `{id}` path segment is accepted for URL/backward compatibility but is **ignored** — the profile returned always belongs to the user identified by the bearer token, regardless of what `{id}` is passed. This prevents one user from viewing another user's profile by changing the id in the URL (IDOR).
**Path Parameter:**
- `id`: Present for URL compatibility only; not used to select the record.
**Response Example:**
```json
{
  "person": {
    "PersonID": 1,
    "FirstName": "John",
    "SecondName": "Doe",
    "ThirdName": "Smith",
    "FourthName": "Brown",
    "BloodTypeName": "A+",
    "EgazetBetakatTaqaddomName": "Some Name",
    // ...other joined fields
  },
  "questions": [
    {
      "QuestionText": "Why do you want to join?",
      "Answer": "To learn and grow."
    },
    // ...more questions
  ]
}
```

---

## Get Person Calendar
### GET `/api/calendar/{id}`
**Description:** Returns calendar events for the **authenticated** user's own group/qetaa membership. The `{id}` path segment is accepted for URL/backward compatibility but is **ignored** — results always belong to the user identified by the bearer token, regardless of what `{id}` is passed.
**Path Parameter:**
- `id`: Present for URL compatibility only; not used to select the record.
**Response Example:**
```json
{
  "events": [
    {
      "EventID": 10,
      "EventName": "Weekly Meeting",
      "EventStartDate": "2025-09-01 18:00:00",
      "EventEndDate": "2025-09-01 20:00:00",
      "EventTypeName": "Meeting",
      "SeasonName": "Season A",
      "SeasonYear": 2025
    }
  ]
}
```

---

## Security Notes
- `/api/show-persons`, `/api/person/{id}`, and `/api/calendar/{id}` never trust a client-supplied user/person id for scoping data. They always use the `PersonID` of the user authenticated via the request's bearer token (`$request->user()->PersonID`). This closes an IDOR (Insecure Direct Object Reference) vulnerability where a caller could previously pass any `id` to read another person's data.

---

## Error Handling
- All endpoints return standard HTTP status codes.
- If a person is not found, `/api/person/{id}` returns:
```json
{
  "error": "Person not found"
}
```

---

## Notes
- All endpoints return JSON responses.
- Make sure to pass required parameters as specified.
- For authentication, use the token returned by `/api/login` in subsequent requests if required.
