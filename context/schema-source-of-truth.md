# Schema source of truth

**Migrations** in `database/migrations/` are authoritative.

`schema.sql` is a historical bootstrap dump and may lag recent packages (WhatsApp campaigns, audit logs, PersonExamMark, identity/enrolment hardening, etc.).

When refreshing a dump for local bootstrap:

```bash
mysqldump --no-data --routines --triggers shamadora_local > schema.sql
```

Never treat `schema.sql` as newer than migrations when they disagree.
