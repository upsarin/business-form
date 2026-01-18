(() => {
    let form = document.getElementById('applyForm');
    let msg = document.getElementById('msg');
    if (!form) return;

    let show = (text, ok) => {
        msg.hidden = false;
        msg.className = 'msg ' + (ok ? 'ok' : 'err');
        msg.textContent = text;
    };

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        msg.hidden = true;

        let fd = new FormData(form);

        try {
            let res = await fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                credentials: 'same-origin'
            });

            let data = await res.json();
            show(data.message || (data.ok ? 'Отправлено' : 'Ошибка'), !!data.ok);

            if (data.ok) {
                form.reset();
                if (window.grecaptcha) grecaptcha.reset();
            }
        } catch {
            show('Ошибка сети. Попробуйте позже.', false);
        }
    });
})();
