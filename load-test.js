import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    stages: [
        { duration: '30s', target: 10 },
        { duration: '30s', target: 20 },
        { duration: '30s', target: 50 },
        { duration: '30s', target: 0 },
    ],
    summaryTrendStats: ['avg', 'min', 'med', 'max', 'p(90)', 'p(95)', 'p(99)'],
    thresholds: {
        http_req_failed: ['rate<0.01'],
    },
};

export default function () {
    const response = http.get('http://host.docker.internal:8000/api/v1/user/products/3');

    check(response, {
        'status is 200': (r) => r.status === 200,
    });

    sleep(1);
}
