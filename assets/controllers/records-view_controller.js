import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['pre', 'modal', 'frame'];
    static values = {
        botId: Number,
    }

    connect() {
    }

    reset() {
        this.preTarget.innerHTML = "";
        this.frameTarget.src="";
    }

    raw(event) {
        const pre = this.preTarget;
        const recordId = event.params['id'];
        const botId = this.botIdValue || event.params['botId'];

        this.reset();
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

    view(event) {
        const frame = this.frameTarget;
        const recordId = event.params['id'];
        const botId = this.botIdValue || event.params['botId'];

        this.reset();
        let modal = new Modal(this.modalTarget);
        modal.show();
        frame.src = '/records/view/' + botId + '/record/' + recordId;
    }

    download (event) {
        const recordId = event.params.id;
        const botId = this.botIdValue || event.params['botId'];
        window.location.href = '/records/download/' + botId + '/record/' + recordId;
    }

    viewHeaders(event) {
        this.reset();
        let headers = event.params['headers'];
        let modal = new Modal(this.modalTarget);
        modal.show();
        this.preTarget.textContent = headers;
    }
}
