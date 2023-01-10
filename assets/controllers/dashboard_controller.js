import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['content'];

    connect() {
    }


    refreshTable() {
    }

    fuzzyTime(input) {
        let out = "n/a";
        if (input === null) return out;
        let date = new Date(input).valueOf() / 1000;
        let now = new Date().valueOf() / 1000;
        let secs = Math.floor(now) - Math.floor(date);
        if (secs < 3600) {
            let mins = Math.floor(secs / 60);
            out = mins + " minute" + (mins != 1 ? 's' : '') + ' ago';
        } else if ((secs > 3600) && (secs < 86400)) {
            let hours = Math.floor(secs / 3600);
            out = hours + " hour" + (hours != 1 ? 's' : '') + ' ago';
        } else {
            let days = Math.floor(secs / 86400);
            out = days + " day" + (days != 1 ? 's' : '') + ' ago';
        }
        return out;
    }

}
