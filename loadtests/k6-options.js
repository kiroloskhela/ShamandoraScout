/**
 * k6 scenario profiles for ShamandoraScout.
 * Keep this file free of HTTP so options stay easy to scan.
 */

export function webSessionCount() {
  return Math.min(Math.max(parseInt(__ENV.WEB_SESSIONS || '1', 10) || 1, 1), 20);
}

export function scenarioOptions(name) {
  const pool = webSessionCount();
  const soak = __ENV.SOAK_DURATION || '90m';
  const setupTimeout = `${Math.max(60, 30 + pool * 22)}s`;

  const common = {
    setupTimeout,
    summaryTrendStats: ['avg', 'min', 'med', 'p(90)', 'p(95)', 'p(99)', 'max'],
  };

  switch (name) {
    case 'smoke':
      return {
        ...common,
        scenarios: {
          smoke: { executor: 'constant-vus', vus: 1, duration: '30s', exec: 'webMix' },
        },
        thresholds: tight(0.05, 1500),
      };

    case 'public':
      return {
        ...common,
        scenarios: {
          public: { executor: 'constant-vus', vus: 50, duration: '5m', exec: 'publicMix' },
        },
        thresholds: tight(0.01, 1000),
      };

    case 'baseline':
      return {
        ...common,
        scenarios: {
          staff: { executor: 'constant-vus', vus: 50, duration: '5m', exec: 'webMix' },
          api: apiProbe('5m'),
        },
        thresholds: tight(0.01, 1000),
      };

    case 'ramp':
      return {
        ...common,
        scenarios: {
          staff: {
            executor: 'ramping-vus',
            exec: 'webMix',
            startVUs: 0,
            stages: [
              { duration: '5m', target: 500 },
              { duration: '2m', target: 500 },
              { duration: '30s', target: 0 },
            ],
          },
        },
        thresholds: loose(),
      };

    case 'spike':
      return {
        ...common,
        scenarios: {
          staff: {
            executor: 'ramping-vus',
            exec: 'webMix',
            startVUs: 10,
            stages: [
              { duration: '10s', target: 10 },
              { duration: '10s', target: 1000 },
              { duration: '1m', target: 1000 },
              { duration: '20s', target: 10 },
            ],
          },
        },
        thresholds: loose(),
      };

    case 'soak':
      return {
        ...common,
        scenarios: {
          staff: { executor: 'constant-vus', vus: 30, duration: soak, exec: 'webMix' },
        },
        thresholds: tight(0.01, 1000),
      };

    default:
      throw new Error(`Unknown SCENARIO="${name}". Use smoke|public|baseline|ramp|spike|soak.`);
  }
}

function apiProbe(duration) {
  return {
    executor: 'constant-arrival-rate',
    exec: 'apiMix',
    rate: 50,
    timeUnit: '1m',
    duration,
    preAllocatedVUs: 2,
    maxVUs: 4,
  };
}

function tight(failRate, p95ms) {
  return {
    unexpected_fail: [`rate<${failRate}`],
    http_req_duration: [`p(95)<${p95ms}`, 'p(99)<2000'],
  };
}

function loose() {
  return {
    unexpected_fail: ['rate<0.05'],
    http_req_duration: ['p(95)<3000', 'p(99)<8000'],
  };
}
