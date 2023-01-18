import { Controller } from '@hotwired/stimulus';
import { Notification } from './notification.js';

function timeFormat(input) {
    let out = "n/a";
    if (input === null) return out;

    let date = new Date(input);
    return date.toUTCString();
}

export default class extends Controller {
    static targets = [ 'botId', 'launchId' ];
    static values = {
        botId: Number,
        launchId: Number,
    };

    connect() {
        this.launchIdTarget.addEventListener('change', (event) => {
            if (!event.target.value) return;
                this.launchIdValue = event.target.value;
                if ((this.launchIdValue) && (this.botIdValue)) {
                    window.location = '/records/list/' + this.botIdValue + '/launch/' + this.launchIdValue + '/offset/0';
                }
        });

        this.botIdTarget.addEventListener('change', (event) => {
            if (!event.target.value) return;
            this.botIdValue = event.target.value;
            this.clearSelectElements(this.launchIdTarget);
            this.launchIdTarget.classList.remove('visually-hidden');

            fetch('/api/robot/launches/' + this.botIdValue, {
                method: 'GET',
                headers: {
                    'Content-Type': 'application/json; charset=UTF-8',
                },
            })
            .then(response => response.json())
            .then(launches => {
                for (let launch of launches) {
                    let option = document.createElement('option');
                    option.text = timeFormat(launch.startTime);
                    option.value = launch.id;
                    this.launchIdTarget.appendChild(option);
                }
            })
            .catch((error) => {
                let notification = new Notification('An error occurred: ' + error, true);
                notification.show();
            });
        });

        fetch('/api/robot/query/all', {
            method: 'GET',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
        })
        .then(response => response.json())
        .then(robots => {
            for (let robot of robots) {
                let option = document.createElement('option')
                option.text = `(${robot.scheme}) ${robot.domainName}`;
                option.value = robot.id;
                this.botIdTarget.appendChild(option)
            }
        })
        .catch((error) => {
            let notification = new Notification('An error occurred: ' + error, true);
            notification.show();
        });
    }

    clearSelectElements(selectElement) {
        let length = selectElement.options.length;
        for (let i = length -1; i >= 1; i--) {
            if (selectElement.options[i].value != "") {
                selectElement.remove(i);
            }
        }
        selectElement.value = "";
    }
}
