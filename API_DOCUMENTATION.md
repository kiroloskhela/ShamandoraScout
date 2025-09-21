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
### GET `/api/show-persons?id={userId}`
**Description:** Returns a list of persons filtered by group membership for the given user ID.
**Query Parameter:**
- `id` (required): The user ID to filter persons by group.
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
**Description:** Returns all joined data for a person by their ID.
**Path Parameter:**
- `id` (required): The person ID.
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
