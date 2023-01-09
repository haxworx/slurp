import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';
import { Notification } from './notification.js';

export default class extends Controller {
    static targets = ['modal', 'confirm', 'button'];
    static values = {
        token: String,
        botId: Number,
    }

    connect() {
    }

    confirm(event) {
        event.preventDefault();
        let confirmText = this.confirmTarget;
        document.addEventListener('keyup', (event) => {
            if (confirmText.value === "delete me") {
                this.buttonTarget.classList.remove("disabled") 
            } else {
                this.buttonTarget.classList.add("disabled");
            }
        });
        this.modal = new Modal(this.modalTarget);
        confirmText.value = "";
        this.buttonTarget.classList.add("disabled");
        this.modal.show();
    }

    remove(event) {
        event.preventDefault();
        let o = {
            'botId': this.botIdValue,
            'token': this.tokenValue,
        };

        fetch('/schedule/delete/' + this.botIdValue, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(o)
        })
        .then(response => response.json()) 
        .then(payload => {
            if (payload.message === 'ok') {
                window.location = '/';
            } else {
                throw "Invalid JSON response";
            }
        })
        .catch((error) => {
            let notification = new Notification("An error occurred: " + error, true);
            notification.show();
        });
        this.modal.hide();
    }
}
