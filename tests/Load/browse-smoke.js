import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
    scenarios: {
        browse: { executor: 'constant-vus', vus: Number(__ENV.VUS || 100), duration: __ENV.DURATION || '2m' },
    },
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(95)<2500'],
    },
};

const baseUrl = __ENV.BASE_URL || 'http://127.0.0.1:8000';

export default function () {
    for (const path of ['/', '/semua-produk']) {
        const response = http.get(`${baseUrl}${path}`, { tags: { flow: 'browse' } });
        check(response, { 'status is successful': (r) => r.status >= 200 && r.status < 400 });
    }
    sleep(1);
}
