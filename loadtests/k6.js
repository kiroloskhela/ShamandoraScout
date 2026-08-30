/**
 * ShamandoraScout load tests (k6).
 *
 * Read-only by default. Login happens in setup() only (throttle:5,1).
 * Default SCENARIO is smoke (1 VU). The API probe (50 req/min) runs only
 * on SCENARIO=baseline so Laravel's 60/min-per-user limiter is not the
 * thing we are measuring. Web VUs are the capacity test.
 *
 *   brew install k6
 *   set -a && source loadtests/k6.env && set +a
 *   k6 run -e SCENARIO=smoke loadtests/k6.js
 */

import http from 'k6/http';
import { check, sleep } from 'k6';
import { Rate, Counter } from 'k6/metrics';
import { scenarioOptions, webSessionCount } from './k6-options.js';

const unexpectedFail = new Rate('unexpected_fail');
const throttled = new Counter('api_throttled');
const liveformClosed = new Counter('liveform_closed');
const authzDenied = new Counter('authz_denied');

const BASE = stripSlash(__ENV.BASE_URL || 'http://127.0.0.1:8000');
const SCENARIO = (__ENV.SCENARIO || 'smoke').toLowerCase();
const PERSON_ID = __ENV.PERSON_ID || '';
const PASSWORD = __ENV.PASSWORD || '';
const SEARCH = 'ahmed';
const UA = 'k6-shamandora-loadtest';

export const options = scenarioOptions(SCENARIO);

export function setup() {
  assertSafeTarget(SCENARIO, BASE);

  const liveform = probeLiveform();
  const prod = /shamandorascout\.com/i.test(BASE);
  if (
    prod &&
    liveform.open &&
    (SCENARIO === 'public' || SCENARIO === 'spike' || SCENARIO === 'ramp') &&
    __ENV.ALLOW_LIVEFORM_LOAD !== '1'
  ) {
    throw new Error(
      'Liveform is OPEN on production. Refusing public/ramp/spike. Set ALLOW_LIVEFORM_LOAD=1 only if you intend this.',
    );
  }

  const needsWeb = SCENARIO !== 'public';
  const needsApi = SCENARIO === 'baseline';

  if (!needsWeb && !needsApi) {
    return { sessions: [], accessToken: '', personId: 0, seasonEventId: 0 };
  }

  if (!PERSON_ID || !PASSWORD) {
    throw new Error('PERSON_ID and PASSWORD are required (copy loadtests/k6.env.example).');
  }

  let accessToken = '';
  let personId = Number(PERSON_ID) || 0;
  let seasonEventId = 0;

  if (needsApi) {
    accessToken = apiLogin().accessToken;
    personId = fetchPersonId(accessToken);
    seasonEventId = resolveSeasonEventId(accessToken, personId);
  }

  const sessions = [];
  if (needsWeb) {
    const pool = webSessionCount();
    for (let i = 0; i < pool; i++) {
      if (i > 0) {
        sleep(16);
      }
      sessions.push(webLogin());
    }
  }

  return { sessions, accessToken, personId, seasonEventId };
}

export function publicMix() {
  browsePublic();
  think();
}

function browsePublic() {
  hit('GET /health', 'GET', '/health', { expect: [200] });
  hit('GET /login-auth', 'GET', '/login-auth', { expect: [200] });
  hit('GET /liveform', 'GET', '/liveform', { expect: [200, 503], liveform: true });
  hit('GET /img/logo', 'GET', '/img/shamandora.webp', { expect: [200] });
  hit('GET /api/version', 'GET', '/api/version/check?platform=android&version=1.0.0', {
    expect: [200, 429],
  });
}

export function webMix(data) {
  const cookies = pickSession(data);
  applyCookies(cookies);

  const roll = Math.random();
  if (roll < 0.18) {
    browsePublic();
    think();
    return;
  }
  if (roll < 0.38) {
    staffGet('/person', cookies, [200, 403]);
    staffGet(`/person?q=${SEARCH}`, cookies, [200, 403]);
    staffGet(`/person/search?q=${SEARCH}`, cookies, [200, 403]);
  } else if (roll < 0.52) {
    staffGet('/', cookies, [200]);
    staffGet('/profile', cookies, [200, 403]);
  } else if (roll < 0.64) {
    staffGet('/team/structure', cookies, [200, 403]);
  } else if (roll < 0.74) {
    staffGet('/person/ShowPersons', cookies, [200, 403]);
  } else if (roll < 0.84) {
    staffGet('/attendance/manage', cookies, [200, 403]);
    staffGet('/attendance/live', cookies, [200, 403]);
    staffGet('/attendance/live/snapshot', cookies, [200, 403]);
  } else if (roll < 0.92) {
    staffGet('/finance', cookies, [200, 403]);
    staffGet('/event-booking-finance/selector', cookies, [200, 403]);
  } else if (roll < 0.97) {
    staffGet('/new-enrolments', cookies, [200, 403]);
    staffGet('/export/served-people', cookies, [200, 403]);
  } else {
    staffGet('/secretary', cookies, [200, 403]);
    staffGet('/custody-requests/my', cookies, [200, 403]);
    staffGet('/place-bookings/my', cookies, [200, 403]);
  }
  think();
}

export function apiMix(data) {
  const token = data.accessToken;
  if (!token) {
    return;
  }

  const id = data.personId || PERSON_ID;
  const eventId = data.seasonEventId;
  const roll = Math.random();

  if (roll < 0.35) {
    apiGet('/api/show-persons', token);
  } else if (roll < 0.48) {
    apiGet('/api/me', token);
  } else if (roll < 0.58) {
    apiGet(`/api/person/${id}`, token);
  } else if (roll < 0.68) {
    apiGet(`/api/calendar/${id}`, token);
  } else if (roll < 0.78) {
    apiGet(`/api/attendance/events?person_id=${id}`, token);
  } else if (roll < 0.86 && eventId) {
    apiGet(`/api/attendance/persons/${eventId}`, token);
  } else if (roll < 0.92) {
    // also the fallback when seasonEventId was not resolved in setup
    apiGet('/api/team-structure', token);
  } else if (roll < 0.96) {
    apiGet('/api/custody/requests', token);
  } else {
    apiGet('/api/curricula', token);
  }
}

function assertSafeTarget(name, url) {
  const prod = /shamandorascout\.com/i.test(url);
  if (prod && __ENV.ALLOW_PROD !== '1') {
    throw new Error(
      `Refusing ${url}. Set ALLOW_PROD=1 and BASE_URL explicitly if you intend to hit production.`,
    );
  }
  if (prod && (name === 'spike' || name === 'ramp') && __ENV.ALLOW_SPIKE_PROD !== '1') {
    throw new Error(
      `Refusing production ${name} (high VU). Set ALLOW_SPIKE_PROD=1 only during a maintenance window.`,
    );
  }
}

function probeLiveform() {
  const res = http.get(`${BASE}/liveform`, {
    headers: { 'User-Agent': UA },
    tags: { name: 'GET /liveform' },
  });
  return { open: res.status === 200 };
}

function apiLogin() {
  const res = http.post(
    `${BASE}/api/login`,
    JSON.stringify({ id: Number(PERSON_ID), password: PASSWORD }),
    {
      headers: jsonHeaders(),
      tags: { name: 'POST /api/login' },
    },
  );
  if (res.status === 429) {
    throw new Error('API login throttled (429). Wait a minute; throttle is 5/min.');
  }
  if (res.status !== 200) {
    throw new Error(`API login failed (${res.status}). Check PERSON_ID/PASSWORD; do not retry-spam (throttle:5,1).`);
  }
  const body = res.json();
  const token = body.access_token;
  if (!token) {
    throw new Error('API login returned no access_token.');
  }
  return { accessToken: token };
}

function fetchPersonId(token) {
  const res = http.get(`${BASE}/api/me`, {
    headers: jsonHeaders(token),
    tags: { name: 'GET /api/me' },
  });
  if (res.status !== 200) {
    throw new Error(`GET /api/me failed (${res.status}). Account may lack api.me.view.`);
  }
  const body = res.json();
  return Number(body.person_id) || Number(PERSON_ID);
}

function resolveSeasonEventId(token, personId) {
  const fromEnv = parseInt(__ENV.SEASON_EVENT_ID || '0', 10);
  if (fromEnv > 0) {
    return fromEnv;
  }

  const res = http.get(`${BASE}/api/attendance/events?person_id=${personId}`, {
    headers: jsonHeaders(token),
    tags: { name: 'GET /api/attendance/events' },
  });
  if (res.status !== 200) {
    return 0;
  }
  const body = res.json();
  if (Array.isArray(body.seasons)) {
    for (const season of body.seasons) {
      if (season.events && season.events.length) {
        return Number(season.events[0].SeasonEventID) || 0;
      }
    }
  }
  return 0;
}

function webLogin() {
  const page = http.get(`${BASE}/login-auth`, {
    headers: { 'User-Agent': UA },
    tags: { name: 'GET /login-auth' },
  });
  if (page.status !== 200) {
    throw new Error(`GET /login-auth failed (${page.status}).`);
  }

  const doc = page.html();
  const csrf =
    doc.find('input[name="_token"]').attr('value') ||
    doc.find('meta[name="csrf-token"]').attr('content');
  if (!csrf) {
    throw new Error('Could not read CSRF token from /login-auth.');
  }

  const cookies = {};
  mergeCookies(page, cookies);
  applyCookies(cookies);

  const posted = http.post(
    `${BASE}/login`,
    {
      _token: csrf,
      person_id: PERSON_ID,
      person_password: PASSWORD,
    },
    {
      headers: { 'User-Agent': UA },
      tags: { name: 'POST /login' },
      redirects: 5,
    },
  );
  mergeCookies(posted, cookies);

  if (posted.status === 429) {
    throw new Error('Web login throttled (429). Wait a minute or lower WEB_SESSIONS.');
  }
  if (looksLikeLogin(posted) || posted.status >= 400) {
    throw new Error(`Web login did not establish a session (status ${posted.status}).`);
  }

  return cookies;
}

function pickSession(data) {
  const sessions = data.sessions || [];
  if (!sessions.length) {
    return {};
  }
  return sessions[(__VU - 1) % sessions.length];
}

function applyCookies(cookies) {
  const jar = http.cookieJar();
  for (const [name, value] of Object.entries(cookies || {})) {
    if (name && value) {
      jar.set(BASE, name, value, { path: '/' });
    }
  }
}

function mergeCookies(res, into) {
  for (const [name, list] of Object.entries(res.cookies || {})) {
    if (list && list.length) {
      into[name] = list[list.length - 1].value;
    }
  }
}

function staffGet(path, cookies, expect) {
  applyCookies(cookies);
  const res = http.get(`${BASE}${path}`, {
    headers: { 'User-Agent': UA },
    tags: { name: `GET ${path.split('?')[0]}` },
    redirects: 5,
  });
  record(res, { name: path, expect, cookies: true });
}

function apiGet(path, token) {
  const res = http.get(`${BASE}${path}`, {
    headers: jsonHeaders(token),
    tags: { name: `GET ${path.split('?')[0]}` },
  });
  record(res, { name: path, expect: [200, 403, 429], api: true });
}

function hit(name, method, path, opts) {
  const res = http.request(method, `${BASE}${path}`, null, {
    headers: { 'User-Agent': UA, Accept: path.startsWith('/api') ? 'application/json' : 'text/html' },
    tags: { name },
  });
  record(res, { name, expect: opts.expect, liveform: opts.liveform });
}

function record(res, opts) {
  const expect = opts.expect || [200];
  if (res.status === 429) {
    throttled.add(1);
  }
  if (opts.liveform && res.status === 503) {
    liveformClosed.add(1);
  }
  if (res.status === 403) {
    authzDenied.add(1);
  }

  const redirectedToLogin = opts.cookies && looksLikeLogin(res);
  const ok = expect.includes(res.status) && !redirectedToLogin;
  unexpectedFail.add(ok ? 0 : 1);
  check(res, {
    [`${opts.name} ${expect.join('|')}`]: () => ok,
  });
}

function looksLikeLogin(res) {
  const body = res.body || '';
  return typeof body === 'string' && body.includes('name="person_id"');
}

function jsonHeaders(token) {
  const headers = {
    'Content-Type': 'application/json',
    Accept: 'application/json',
    'User-Agent': UA,
  };
  if (token) {
    headers.Authorization = `Bearer ${token}`;
  }
  return headers;
}

function think() {
  const aggressive = SCENARIO === 'ramp' || SCENARIO === 'spike';
  sleep(aggressive ? 0.2 : 0.8 + Math.random());
}

function stripSlash(url) {
  return url.endsWith('/') ? url.slice(0, -1) : url;
}
