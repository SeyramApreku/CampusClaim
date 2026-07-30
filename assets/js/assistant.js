document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('assistant-form');
    const input = document.getElementById('assistant-question');
    const messages = document.getElementById('assistant-messages');
    if (!form || !input || !messages) return;

    function addMessage(text, kind, sources) {
        const node = document.createElement('div');
        node.className = 'assistant-message assistant-message-' + kind;
        const body = document.createElement('p');
        body.textContent = text.replace(/\s*\[Item #\d+\]/g, '');
        node.appendChild(body);

        if (sources && sources.length) {
            const heading = document.createElement('strong');
            heading.className = 'assistant-sources-heading';
            heading.textContent = sources.length === 1 ? 'Possible match' : 'Possible matches';
            node.appendChild(heading);

            const list = document.createElement('div');
            list.className = 'assistant-sources';
            sources.forEach(function (source, index) {
                const card = document.createElement('a');
                card.className = 'assistant-source-card';
                card.href = source.url;

                const number = document.createElement('span');
                number.className = 'assistant-source-number';
                number.textContent = index + 1;

                const details = document.createElement('span');
                details.className = 'assistant-source-details';

                const title = document.createElement('strong');
                title.textContent = source.title;

                const meta = document.createElement('span');
                meta.className = 'assistant-source-meta';
                meta.textContent = [
                    source.type.charAt(0).toUpperCase() + source.type.slice(1),
                    source.location,
                    source.date
                ].filter(Boolean).join(' · ');

                const action = document.createElement('span');
                action.className = 'assistant-source-action';
                action.textContent = 'View report →';

                details.appendChild(title);
                details.appendChild(meta);
                card.appendChild(number);
                card.appendChild(details);
                card.appendChild(action);
                list.appendChild(card);
            });
            node.appendChild(list);
        }
        messages.appendChild(node);
        messages.scrollTop = messages.scrollHeight;
        return node;
    }

    form.addEventListener('submit', async function (event) {
        event.preventDefault();
        const question = input.value.trim();
        if (!question) return;

        addMessage(question, 'user');
        input.value = '';
        const pending = addMessage('Searching open reports…', 'bot');
        const button = form.querySelector('button');
        button.disabled = true;

        try {
            const response = await fetch(window.CAMPUSCLAIM_ASSISTANT.endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': window.CAMPUSCLAIM_ASSISTANT.csrfToken
                },
                body: JSON.stringify({ question: question })
            });
            const data = await response.json();
            pending.remove();
            if (!response.ok) throw new Error(data.error || 'Request failed.');
            addMessage(data.answer, 'bot', data.sources || []);
        } catch (error) {
            pending.remove();
            addMessage(error.message || 'The assistant is unavailable.', 'error');
        } finally {
            button.disabled = false;
            input.focus();
        }
    });
});
