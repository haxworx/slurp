import { Controller } from '@hotwired/stimulus';
import { Notification } from './notification.js';
import { Robots } from './robots.js';

export default class extends Controller {
    static targets = ['content'];

    connect() {
        console.log("hello world");
        this.refreshTable();
    }


    refreshTable() {
        let content = this.contentTarget;
        content.innerHTML = "";
        let query = new Robots((robots) => {
            this.drawTable(robots);
        });
        query.get();
    }

    drawTable(robots) {
        let tb, thead, th, tr, tbody = [ null, null, null, null, null ];

        tb = document.createElement('table');
        tb.classList.add('table');
        tb.classList.add('table-striped');

        thead = document.createElement('thead');
        tr = document.createElement('tr');
        th = document.createElement('th');
        th.textContent = "id";
        tr.appendChild(th);

        th = document.createElement('th');
        th.textContent = "domain";
        tr.appendChild(th);

        th = document.createElement('th');
        th.textContent = "agent";
        tr.appendChild(th);

        th = document.createElement('th');
        th.textContent = "start";
        tr.appendChild(th);

        th = document.createElement('th');
        th.textContent = "finished";
        tr.appendChild(th);

        th = document.createElement('th');
        th.textContent = "running";
        tr.appendChild(th);

        th = document.createElement('th');
        th.textContent = "actions";
        tr.appendChild(th);
        thead.appendChild(tr);
        tb.appendChild(thead);

        tbody = document.createElement('tbody');
        tb.appendChild(tbody);

        robots.forEach(robot => {
            let tr = document.createElement('tr');
            let td = document.createElement('td');
            td.textContent = robot.id;
            tr.appendChild(td);

            td = document.createElement('td');
            td.textContent = robot.domainName;
            tr.appendChild(td);

            td = document.createElement('td');
            td.textContent = robot.userAgent;
            tr.appendChild(td);

            td = document.createElement('td');
            td.textContent = this.timeFormat(robot.startTime);
            tr.appendChild(td);

            td = document.createElement('td');
            td.textContent = this.fuzzyTime(robot.endTime);
            tr.appendChild(td);

            td = document.createElement('td');
            td.textContent = (robot.isRunning) ? "true" : false;
            tr.appendChild(td);

            td = document.createElement('td');
            td.textContent = '';
            let a = document.createElement('a');
            a.href = '/schedule/edit/' + robot.id
            let i = document.createElement(i);
            i.classList.add('fa-solid');
            i.classList.add('fa-pen-to-square');
            a.appendChild(i);
            td.appendChild(a);
            tr.appendChild(td);
            tbody.appendChild(tr);
        });
        let content = this.contentTarget;
        content.appendChild(tb);
    }

    timeFormat(input) {
        let out = "n/a";
        if (input === null) return out;

        let date = new Date(input);

        return date.toLocaleTimeString('UTC');
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
