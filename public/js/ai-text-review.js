(() => {
    'use strict';

    const configElement = document.getElementById('ai-text-review-config');
    if (!configElement || !window.Swal) return;
    const config = JSON.parse(configElement.textContent);
    const Swal = {
        fire: options => window.Swal.fire({
            ...options,
            target: document.querySelector('dialog[open]') || document.body,
            didOpen: options.didOpen ? popup => { if (popup === window.Swal.getPopup()) options.didOpen(popup); } : undefined,
        }),
        showLoading: () => window.Swal.showLoading(),
        close: () => window.Swal.close(),
    };
    const labels = {
        purpose: 'Objet', observations: 'Observations / Recommandations', layout_content: 'Mise en page',
        description: 'Description', name: 'Activité', deliverable: 'Livrable attendu',
        work_description: 'Travaux réalisés', difficulties: 'Difficultés', impact: 'Impact',
        proposed_solution: 'Solution proposée', title: 'Action', completion_comment: 'Commentaire de clôture',
    };

    function element(tag, classes, text) {
        const node = document.createElement(tag);
        node.className = classes;
        if (text !== undefined) node.textContent = text;
        return node;
    }

    async function request(url, method = 'GET', body) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;
        const response = await fetch(url, {
            method, credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf || '' },
            ...(body ? { body: JSON.stringify(body) } : {}),
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
            if (response.status === 401 || response.status === 419) throw new Error('Votre session a expiré. Copiez votre texte avant de vous reconnecter.');
            throw new Error(Object.values(data.errors || {}).flat()[0] || data.message || 'Le service IA est indisponible. Votre texte a été conservé.');
        }
        return data;
    }

    function highlighted(original, corrected, side) {
        const before = original.split(/(\s+)/);
        const after = corrected.split(/(\s+)/);
        const container = element('div', 'whitespace-pre-wrap break-words rounded-lg border border-gray-200 bg-gray-50 p-3 text-left text-sm text-gray-800');
        if (before.length * after.length > 400000) {
            container.textContent = side === 'before' ? original : corrected;
            return container;
        }
        const matrix = Array.from({ length: before.length + 1 }, () => new Uint16Array(after.length + 1));
        for (let i = before.length - 1; i >= 0; i--) {
            for (let j = after.length - 1; j >= 0; j--) {
                matrix[i][j] = before[i] === after[j] ? 1 + matrix[i + 1][j + 1] : Math.max(matrix[i + 1][j], matrix[i][j + 1]);
            }
        }
        let i = 0;
        let j = 0;
        while (i < before.length || j < after.length) {
            if (i < before.length && j < after.length && before[i] === after[j]) {
                container.append(document.createTextNode(before[i]));
                i++; j++;
            } else if (j < after.length && (i === before.length || matrix[i][j + 1] >= matrix[i + 1][j])) {
                if (side === 'after') container.append(element('mark', 'bg-green-100 text-green-900', after[j]));
                j++;
            } else {
                if (side === 'before') container.append(element('mark', 'bg-red-100 text-red-900', before[i]));
                i++;
            }
        }
        return container;
    }

    async function compare(fields, { saving = false, persist = false } = {}) {
        const content = element('div', 'flex flex-col gap-5');
        const selections = {};
        let changes = 0;
        for (const [field, result] of Object.entries(fields)) {
            if (!result.changed && !result.note) continue;
            const section = element('section', 'flex flex-col gap-2 text-left');
            section.append(element('h3', 'font-semibold text-gray-900', labels[field] || field));
            if (result.after_validation && result.changed) {
                section.append(element('p', 'rounded bg-amber-50 p-2 text-sm text-amber-900', 'Correction après validation : la version originale sera conservée. Les signatures existantes concernent la version originale.'));
            }
            if (result.note) section.append(element('p', 'rounded bg-blue-50 p-2 text-sm text-blue-900', result.note));
            if (result.changed) {
                changes++;
                const columns = element('div', 'grid grid-cols-1 gap-3 md:grid-cols-2');
                for (const [side, title] of [['before', 'Texte original'], ['after', 'Proposition IA']]) {
                    const column = element('div', 'flex min-w-0 flex-col gap-1');
                    column.append(element('p', 'text-xs font-semibold text-gray-500', title), highlighted(result.original, result.corrected, side));
                    columns.append(column);
                }
                const label = element('label', 'flex cursor-pointer items-center gap-2 text-sm text-gray-700');
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox'; checkbox.checked = true;
                selections[field] = checkbox;
                label.append(checkbox, document.createTextNode('Appliquer cette correction'));
                section.append(columns, label);
            } else {
                section.append(element('p', 'text-sm text-gray-600', 'Aucune version corrigée n’a été proposée : le texte reste inchangé.'));
                section.append(element('p', 'text-xs font-semibold text-gray-500', 'Texte actuel'));
                section.append(element('div', 'whitespace-pre-wrap break-words rounded-lg border border-gray-200 bg-gray-50 p-3 text-sm text-gray-800', result.original));
            }
            content.append(section);
        }
        if (!content.childNodes.length) {
            if (!saving) await Swal.fire({ icon: 'success', title: 'Aucune correction nécessaire', text: 'Ce texte est déjà clair et correctement rédigé.' });
            return { proceed: true, selected: [] };
        }
        const result = await Swal.fire({
            title: changes ? 'Relire avant d’appliquer' : 'Précisions à vérifier', html: content, width: '64rem',
            showCancelButton: saving || changes > 0, showDenyButton: changes > 0, allowOutsideClick: false,
            confirmButtonText: changes ? (saving || persist ? 'Appliquer et enregistrer' : 'Appliquer au formulaire') : (saving ? 'Enregistrer mon texte' : 'Fermer'),
            denyButtonText: saving ? 'Enregistrer sans correction' : 'Conserver mon texte',
            cancelButtonText: saving ? 'Revenir au formulaire' : 'Annuler',
        });
        return { proceed: result.isConfirmed || result.isDenied, selected: result.isConfirmed ? Object.keys(selections).filter(field => selections[field].checked) : [] };
    }

    function formContext(form) {
        const path = new URL(form.action, window.location.href).pathname;
        for (const [suffix, type] of [['activities', 'project-activities'], ['work-logs', 'work-logs'], ['blockers', 'project-blockers'], ['actions', 'project-actions']]) {
            if (new RegExp(`/project-trackings/\\d+/${suffix}(?:/\\d+)?$`).test(path)) {
                return { type, id: path.match(new RegExp(`/${suffix}/(\\d+)$`))?.[1] || null };
            }
        }
        const match = path.match(/\/(surveys|maintenances|intervention-utes|project-trackings|project-activities|project-blockers|project-actions)\/(\d+)(?:\/(?:layout|status))?$|\/(surveys|maintenances|intervention-utes|project-trackings)$/);
        return match ? { type: match[1] || match[3], id: match[2] || null } : null;
    }

    function fieldAccessor(input) {
        if (input.name === 'layout_content') {
            const editor = input.form.querySelector('.ql-container');
            const quill = editor && window.Quill?.find(editor);
            if (!quill) return null;
            return { get: () => JSON.stringify(quill.getContents()), set: value => { quill.setContents(JSON.parse(value)); input.value = value; } };
        }
        return { get: () => input.value, set: value => { input.value = value; input.dispatchEvent(new Event('input', { bubbles: true })); } };
    }

    function rememberToken(form, field, token) {
        form.querySelectorAll('input[data-ai-token]').forEach(input => { if (input.dataset.aiToken === field) input.remove(); });
        if (!token) return;
        const input = document.createElement('input');
        input.type = 'hidden'; input.name = `_ai_reviews[${field}]`; input.value = token; input.dataset.aiToken = field;
        form.append(input);
    }

    async function reviewForm(form, context, inputs, saving) {
        const fields = {};
        const accessors = {};
        for (const input of inputs) {
            const accessor = fieldAccessor(input);
            if (!accessor) continue;
            const value = accessor.get();
            if (!value.trim() || (saving && (input.dataset.aiLastReviewed === value || input.dataset.aiInitial === value))) continue;
            fields[input.name] = value;
            accessors[input.name] = accessor;
        }
        if (!Object.keys(fields).length) return true;
        const data = await request(config.previewUrl, 'POST', { ...context, fields });
        const result = await compare(data.fields, { saving });
        if (!result.proceed) return false;
        for (const [field, reviewed] of Object.entries(data.fields)) {
            if (accessors[field].get() !== fields[field]) throw new Error('Le texte a changé pendant la relecture. Relancez la vérification.');
            const input = inputs.find(input => input.name === field);
            if (result.selected.includes(field)) {
                accessors[field].set(reviewed.value);
                rememberToken(form, field, reviewed.token);
            }
            input.dataset.aiLastReviewed = accessors[field].get();
        }
        return true;
    }

    function initializeForms() {
        document.querySelectorAll('main form').forEach(form => {
            const context = formContext(form);
            if (!context) return;
            const inputs = [...form.elements].filter(input => config.fields[context.type]?.includes(input.name) && (input.tagName === 'TEXTAREA' || input.tagName === 'INPUT'));
            if (!inputs.length) return;
            for (const [name, value] of [['_ai_type', context.type], ['_ai_id', context.id || '']]) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden'; hidden.name = name; hidden.value = value;
                form.append(hidden);
            }
            if (config.previous?.type === context.type && String(config.previous.id || '') === String(context.id || '')) {
                for (const [field, token] of Object.entries(config.previous.tokens || {})) {
                    if (config.fields[context.type].includes(field)) rememberToken(form, field, token);
                }
            }
            let saving = false;
            let bypass = false;
            for (const input of inputs) {
                if (context.id) input.dataset.aiInitial = fieldAccessor(input)?.get() ?? input.value;
                const button = element('button', 'mt-2 inline-flex items-center gap-1 rounded-lg border border-sky-200 bg-sky-50 px-2 py-1 text-xs font-semibold text-sky-700 hover:bg-sky-100 focus-visible:ring-2 focus-visible:ring-sky-500', '✦ IA');
                button.type = 'button'; button.title = 'Améliorer ce texte avec l’IA'; button.setAttribute('aria-label', button.title);
                input.insertAdjacentElement('afterend', button);
                button.addEventListener('click', async () => {
                    if (saving) return;
                    button.disabled = true; button.textContent = 'Relecture…';
                    try { await reviewForm(form, context, [input], false); }
                    catch (error) { await Swal.fire({ icon: 'info', title: 'Texte conservé', text: error.message }); }
                    finally { button.disabled = false; button.textContent = '✦ IA'; }
                });
            }
            form.addEventListener('submit', async event => {
                if (bypass) { bypass = false; return; }
                if (!config.onSave) return;
                event.preventDefault(); event.stopImmediatePropagation();
                if (saving) return;
                saving = true;
                const submitter = event.submitter;
                if (submitter) submitter.disabled = true;
                let proceed = false;
                Swal.fire({ title: 'Relecture de vos textes…', text: 'Vous pourrez accepter les corrections ou conserver vos textes.', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
                try {
                    proceed = await reviewForm(form, context, inputs, true);
                    Swal.close();
                } catch (error) {
                    const result = await Swal.fire({ icon: 'info', title: 'Votre texte est conservé', text: error.message, showCancelButton: true, confirmButtonText: 'Enregistrer sans correction', cancelButtonText: 'Revenir au formulaire' });
                    proceed = result.isConfirmed;
                } finally {
                    saving = false;
                    if (submitter) submitter.disabled = false;
                }
                if (proceed) { bypass = true; form.requestSubmit(submitter || undefined); }
            }, true);
        });
    }

    document.addEventListener('click', async event => {
        const button = event.target.closest('[data-ai-saved], [data-ai-history]');
        if (!button || button.disabled) return;
        button.disabled = true;
        try {
            if (button.dataset.aiHistory) {
                await showHistory(button.dataset.aiHistory);
                return;
            }
            Swal.fire({ title: 'Relecture du texte…', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
            const data = await request(button.dataset.aiSaved, 'POST', { field: button.dataset.aiField });
            const result = await compare(data.fields, { persist: true });
            if (result.selected.length) {
                Swal.fire({ title: 'Enregistrement…', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                await request(button.dataset.aiApply, 'PUT', { token: data.fields[button.dataset.aiField].token });
                window.location.reload();
            }
        } catch (error) {
            await Swal.fire({ icon: 'info', title: 'Texte conservé', text: error.message });
        } finally { button.disabled = false; }
    });

    async function showHistory(url) {
        const data = await request(url);
        const content = element('div', 'flex flex-col gap-4 text-left');
        for (const revision of data.revisions) {
            const item = element('section', 'flex flex-col gap-2 rounded-lg border border-gray-200 p-3');
            item.append(element('p', 'font-semibold', `${labels[revision.field] || revision.field} · ${revision.author} · ${revision.date}`));
            if (revision.after_validation) item.append(element('p', 'text-sm text-amber-800', 'Correction après validation — la signature concerne la version originale.'));
            item.append(highlighted(revision.original, revision.corrected, 'before'), highlighted(revision.original, revision.corrected, 'after'));
            const link = element('a', 'text-sm text-sky-700 underline', 'Télécharger l’état original du document');
            link.href = revision.snapshot_url;
            item.append(link);
            content.append(item);
        }
        if (!data.revisions.length) content.append(element('p', '', 'Aucune correction IA enregistrée pour ce document.'));
        const result = await Swal.fire({ title: 'Historique des corrections IA', html: content, width: '64rem', confirmButtonText: data.next_page_url ? 'Page suivante' : 'Fermer', showCancelButton: !!data.next_page_url, cancelButtonText: 'Fermer' });
        if (result.isConfirmed && data.next_page_url) await showHistory(data.next_page_url);
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initializeForms);
    else initializeForms();
})();
