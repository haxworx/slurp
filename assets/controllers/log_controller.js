import { Controller } from '@hotwired/stimulus';
import { Notification } from './notification.js';

export default class extends Controller {
    static targets = [ 'botId', 'launchId', 'text', 'spinner' ];
    static values = {
        token: String,
        botId: Number,
        launchId: Number,
    };

    connect() {
        this.launchIdTarget.addEventListener('change', (event) => {
            if (!event.target.value) return;
            this.textTarget.classList.remove('visually-hidden');
            this.textTarget.innerHTML = "";
            this.launchIdValue = event.target.value;
            if ((this.launchIdValue) && (this.botIdValue)) {
                this.startLog();
            }
        });

        this.botIdTarget.addEventListener('change', (event) => {
            if (!event.target.value) return;
            this.clearSelectElements(this.launchIdTarget);
            this.launchIdTarget.classList.remove('visually-hidden');
            this.spinnerTarget.classList.add('visually-hidden');
            this.textTarget.classList.add('visually-hidden');
            this.textTarget.innerHTML = "";
            this.botIdValue = event.target.value;
            fetch('/api/robot/launches/' + this.botIdValue, {
                method: 'GET',
                headers: {
                    'Content-Type':'application/json; charset=UTF-8',
                },
            })
            .then(response => response.json())
            .then(launches => {
                for (let launch of launches) {
                    let option = document.createElement('option')
                    option.text = launch.startTime;
                    option.value = launch.id;
                    this.launchIdTarget.appendChild(option);
                }
            })
            .catch((error) => {
                let notification = new Notification('An error occurred: '+ error, true);
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
                option.text = robot.domainName;
                option.value = robot.id;
                this.botIdTarget.appendChild(option);
            }
        })
        .catch((error) => {
            let notification = new Notification('There was a network error ' + error, true);
            notification.show();
        });
    }

    startLog() {
        if (this.interval) {
            clearInterval(this.interval);
        }
        this.dataTime = null;
        this.postObj = {
            botId: this.botIdValue,
            launchId: this.launchIdValue,
            token: this.tokenValue,
            lastId: 0,
        };
        this.downloadLog();
        this.interval = setInterval(this.downloadLog.bind(this), 5000);
    }

    downloadLog() {
        fetch('/api/log', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
            },
            body: JSON.stringify(this.postObj)
        })
        .then(response => response.json())
        .then(obj => {
            this.postObj = obj;
            if ((this.dataTime) && (((Date.now() / 1000) - this.dataTime) >= 5.0)) {
                this.spinnerTarget.classList.add('visually-hidden');
            }
            if (!this.postObj.logs) return;
            this.textTarget.innerHTML = this.textTarget.innerHTML + this.postObj.logs;
            this.textTarget.scrollTop = this.textTarget.scrollHeight;
            delete(this.postObj.logs);
            this.dataTime = Math.floor(Date.now() / 1000);
            this.spinnerTarget.classList.remove('visually-hidden');

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
