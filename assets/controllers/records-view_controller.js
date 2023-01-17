import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['pre', 'modal'];
    static values = {
        botId: Number,
    }

    connect() {
    }

    show(event) {
        const pre = this.preTarget;
        const recordId = event.params['id'];
        const botId = this.botIdValue || event.params['botId'];

        fetch('/records/download/' + botId + '/record/' + recordId, {
            method: 'GET',
        })
        .then(response => response.text())
        .then(data => {
            let modal = new Modal(this.modalTarget);
            modal.show();
            pre.textContent = data;
        })
        .catch((error) => {
            console.error('Error:', error);
            let notification = new Notification("There was a network error.", true);
            notification.show();
        });
    }

    download (event) {
        const recordId = event.params.id;
        const botId = this.botIdValue || event.params['botId'];
        window.location.href = '/records/download/' + botId + '/record/' + recordId;
    }
}
