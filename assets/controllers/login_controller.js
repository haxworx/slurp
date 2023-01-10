import { Controller } from '@hotwired/stimulus';
import { Theme } from './theme.js';

export default class extends Controller {
    static targets = ['form'];

    connect() {
        let form = this.formTarget;
        form.addEventListener('submit', (event) => {
            let theme = new Theme();
            theme.displayLoadingSpinner();
            document.body.appendChild(form);
            form.style.display = "none";
        });
    }
}
