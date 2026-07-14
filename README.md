# ShamandoraScout

## Deployment

Deploys to the VPS are handled automatically by GitHub Actions
(`.github/workflows/deploy.yml`) on every push to `main`. This is the
sole deploy path — there is no HTTP webhook listener; a previous
`webhook.js` script that triggered deploys over an unauthenticated
HTTP endpoint with a hardcoded secret has been removed for security
reasons.
