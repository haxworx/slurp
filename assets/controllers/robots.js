import { Notification } from './notification.js';

export class Robots {
    constructor(callback) {
        this.callback = callback;
    }

    get() {
        fetch('/api/robot/query/all', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
        })
        .then (response => response.json())
        .then (robots => {
            this.callback(robots);
        })
        .catch((error) => {
            this.networkError(error);
        });
    }

    networkError(error) {
        let notification = new Notification('There was a network error', true);
        notification.show();
        console.error(error);
    }
}
