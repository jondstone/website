(() => {
  /**
   * Initialize the comment system container and configuration.
   */
  const root = document.getElementById('ugc-comments');
  if (!root) return;

  const getCsrf = () => document.getElementById('ugc-csrf')?.value || '';
  const getTiming = () => document.getElementById('ugc-timing')?.value || '';
  const pageKey = root.dataset.pageKey || location.pathname;
  const apiBase = root.dataset.apiBase || 'public';

  /**
   * Error Mapping
   */
  const errorMap = {
    method_not_allowed: 'Invalid request method, please try again.',
    invalid_encoding: 'Invalid text was detected, please try again.',
    timing: 'Comment submitted too quickly, please try again shortly.',
    csrf: 'Security verification failed. Refresh and try again.',
    invalid_page_key: 'Invalid page.',
    invalid_author_name: 'Name is invalid.',
    invalid_body_text: 'Comment length is invalid.',
    rate_limit: 'You are posting too quickly. Please wait.',
    rejected: 'Your comment was rejected.',
    server_error: 'Server error. Try again later.'
  };

  /**
   * Application state for managing comments, likes, and UI focus.
   */
  const state = {
    liked: new Set(),
    sort: 'newest',
    byId: new Map(),
    children: new Map(),
    roots: [],
    replyBoxOpenFor: null
  };

  /**
   * Escapes HTML special characters to prevent XSS.
   * @param {string} s 
   * @returns {string}
   */
  const esc = (s) => (s ?? '').toString()
    .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;').replace(/'/g,'&#039;');

  /**
   * Validates name and comment body length requirements.
   * @param {string} name 
   * @param {string} body 
   * @returns {string|null}
   */
  const validateComment = (name, body) => {
    if ((name || '').trim().length < 3) return 'Error: name field must contain at least 3 characters.';
    if ((body || '').trim().length < 7) return 'Error: discussion field must be at least 7 characters.';
    return null;
  };

  /**
   * Generates a unique hue value based on the author's name.
   */
  const getNameHue = (name) => {
    let h = 0;
    for (let i = 0; i < name.length; i++) h = (h * 31 + name.charCodeAt(i)) >>> 0;
    return h % 360;
  };

  /**
   * Generates a unique HSL background color based on the author's name.
   */
  const avatarBg = (name) => `hsl(${getNameHue(name)} 70% 92%)`;

  /**
   * Generates a unique HSL foreground color based on the author's name.
   */
  const avatarFg = (name) => `hsl(${getNameHue(name)} 45% 25%)`;

  /**
   * Returns up to two initials for a given name.
   */
  const initials = (name) => {
    const t = (name || '').trim();
    if (!t) return '?';
    const parts = t.split(/\s+/).slice(0,2);
    return parts.map(p => p[0]?.toUpperCase() || '').join('');
  };

  /**
   * Parses various date strings into a Date object.
   */
  const parseDate = (s) => {
    if (!s) return new Date(0);
    const iso = s.includes('T') ? s : s.replace(' ', 'T');
    const d = new Date(iso);
    return isNaN(d.getTime()) ? new Date(0) : d;
  };

  /**
   * Returns a relative time string (e.g., "today", "2days ago").
   */
  const relTime = (createdAt) => {
    const d = parseDate(createdAt);
    const now = new Date();
    const diffMs = now - d;
    const diffH = diffMs / 36e5;
    if (diffH < 24) return 'today';
    const diffD = Math.floor(diffH / 24);
    if (diffD < 7) return `${diffD} day${diffD===1?'':'s'} ago`;
    const diffW = Math.floor(diffD / 7);
    if (diffW < 4) return `${diffW} week${diffW===1?'':'s'} ago`;
    const diffM = Math.floor(diffD / 30);
    if (diffM < 12) return `${diffM} month${diffM===1?'':'s'} ago`;
    const diffY = Math.floor(diffD / 365);
    return `${diffY} yr${diffY===1?'':'s'} ago`;
  };

  /**
   * Generates a sorting key based on the current sort preference.
   */
  const sortKey = (c) => {
    const likes = Number(c.like_count || 0);
    const t = parseDate(c.created_at).getTime();
    if (state.sort === 'newest') return [-t, 0];
    if (state.sort === 'oldest') return [t, 0];
    return [-likes, -t];
  };

  /**
   * Transforms a flat array of comments into a parent-child tree structure.
   */
  const buildTree = (rows) => {
    state.byId.clear();
    state.children.clear();
    state.roots = [];

    // Map comments by ID and filter visibility.
    for (const r of rows) {
      if (r.status && r.status !== 'visible') continue;
      state.byId.set(Number(r.id), r);
      state.children.set(Number(r.id), []);
    }
    // Link children to parents or identify roots.
    for (const r of state.byId.values()) {
      const id = Number(r.id);
      const pid = (r.parent_id === null || r.parent_id === undefined || r.parent_id === '') ? null : Number(r.parent_id);
      if (pid === null || !state.byId.has(pid)) {
        state.roots.push(id);
      } else {
        state.children.get(pid).push(id);
      }
    }
  };

  /**
   * Sorts an array of comment IDs based on the sortKey criteria.
   */
  const sortIds = (ids) => {
    return [...ids].sort((a,b) => {
      const ca = state.byId.get(a);
      const cb = state.byId.get(b);
      const ka = sortKey(ca);
      const kb = sortKey(cb);
      if (ka[0] !== kb[0]) return ka[0] < kb[0] ? -1 : 1;
      if (ka[1] !== kb[1]) return ka[1] < kb[1] ? -1 : 1;
      return a - b;
    });
  };

  /**
   * Resets a message element to hidden state.
   */
  const resetMessage = (msgEl) => {
    if (!msgEl) return;
    msgEl.style.display = 'none';
    msgEl.textContent = '';
    msgEl.style.color = '';
  };

  /**
   * Displays an error message in the specified element.
   */
  const showError = (msgEl, message) => {
    if (!msgEl) return;
    msgEl.textContent = message;
    msgEl.style.color = '#c40000';
    msgEl.style.display = 'block';
  };

  /**
   * Submits form data via AJAX to the specified action URL.
   */
  const postFormAjax = async (form) => {
    const btn = form.querySelector('button[type="submit"]');
    if (btn) btn.disabled = true;

    const fd = new FormData(form);
    fd.set('csrf', getCsrf());
    fd.set('timing', getTiming());
    fd.set('page_key', pageKey);

    try {
      const res = await fetch(form.action, {
        method: 'POST',
        body: fd,
        credentials: 'same-origin'
      });

      const data = await res.json().catch(() => null);

      if (!data || data.ok !== true) {
        return { ok: false, error: data?.error || 'server_error' };
      }

      return data;
    } finally {
      if (btn) btn.disabled = false;
    }
  };

  /**
   * Sends email notification asynchronously (fire-and-forget).
   */
  const notifyEmail = (commentId, authorName, bodyText) => {
    const fd = new FormData();
    fd.append('csrf', getCsrf());
    fd.append('page_key', pageKey);
    fd.append('comment_id', commentId);
    fd.append('author_name', authorName);
    fd.append('body_text', bodyText);

    // Fire and forget - don't wait for response
    fetch(`${apiBase}/comment-notify.php`, {
      method: 'POST',
      body: fd,
      credentials: 'same-origin'
    }).catch(() => {}); // Silently ignore errors
  };

  /**
   * Builds the main UI structure and handles top-level events.
   */
  const ui = () => {
    const count = state.byId.size;

    root.innerHTML = `
      <div class="ugc-top">
        <div class="ugc-title">${count} Comment${count===1?'':'s'}</div>
        <div class="ugc-sort">
          <button type="button" data-sort="popular" class="${state.sort==='popular'?'active':''}">Popular</button>
          <button type="button" data-sort="newest" class="${state.sort==='newest'?'active':''}">Newest</button>
          <button type="button" data-sort="oldest" class="${state.sort==='oldest'?'active':''}">Oldest</button>
        </div>
      </div>
      <hr class="ugc-hr">

      <div class="ugc-compose">
        <form id="ugc-form" method="post" action="${esc(apiBase)}/comment-submit.php">
            <input type="hidden" name="parent_id" value="">

            <div class="ugc-compose-box">
                <textarea name="body_text" id="ugc-body" placeholder="Join the discussion..."></textarea>
            </div>

            <div class="ugc-compose-row">
                <div id="ugc-compose-msg" class="ugc-muted" style="display:none;"></div>
                <input name="author_name" id="ugc-name" placeholder="Name" maxlength="80">
                <button type="submit" id="ugc-submit">Submit</button>
            </div>
        </form>
    </div>

      <div class="ugc-list" id="ugc-list"></div>
    `;

    // Setup sort button listeners.
    root.querySelectorAll('[data-sort]').forEach(btn => {
      btn.addEventListener('click', () => {
        state.sort = btn.dataset.sort;
        renderList();
        root.querySelectorAll('[data-sort]').forEach(b => b.classList.toggle('active', b.dataset.sort === state.sort));
      });
    });

    const form = root.querySelector('#ugc-form');
    const msg = root.querySelector('#ugc-compose-msg');

    // Handle primary comment submission.
    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const bodyEl = root.querySelector('#ugc-body');
        const nameEl = root.querySelector('#ugc-name');
        const body = (bodyEl?.value || '').trim();
        const name = (nameEl?.value || '').trim();

        resetMessage(msg);

        const err = validateComment(name, body);
        if (err) {
            showError(msg, err);
            return;
        }

        const result = await postFormAjax(form);
        if (!result.ok) {
          showError(msg, errorMap[result.error] || 'Unexpected error.');
          return;
        }

        // Send email notification asynchronously (non-blocking)
        notifyEmail(result.id, name, body);

        bodyEl.value = '';
        state.replyBoxOpenFor = null;
        await load();
    });

    renderList();
  };

  /**
   * Recursively generates HTML for a single comment and its replies.
   */
  const renderComment = (id) => {
    const c = state.byId.get(id);
    if (!c) return '';

    const kids = sortIds(state.children.get(id) || []);
    // Render the inline reply form if this comment is the current reply target.
    const replyBox = (state.replyBoxOpenFor === id) ? `
    <div class="ugc-replybox">
        <form method="post" action="${esc(apiBase)}/comment-submit.php" data-reply-form="${id}">
            <input type="hidden" name="parent_id" value="${id}">

            <div class="ugc-replybox-box">
                <textarea name="body_text" placeholder="Reply..."></textarea>
            </div>

            <div class="ugc-replybox-row">
                <div class="ugc-reply-msg" style="display:none;"></div>
                <input name="author_name" placeholder="Name" maxlength="80">
                <button type="submit">Reply</button>
            </div>
        </form>
    </div>
    ` : '';

    const repliesHtml = kids.length ? `<div class="ugc-replies">${kids.map(k => renderComment(k)).join('')}</div>` : '';

    return `
      <div class="ugc-item" data-id="${id}">
        <div class="ugc-avatar" style="background:${esc(avatarBg(c.author_name||''))};color:${esc(avatarFg(c.author_name||''))}">${esc(initials(c.author_name))}</div>
        <div class="ugc-body">
          <div class="ugc-meta">
            <div class="ugc-name">${esc(c.author_name)}</div>
            <div class="ugc-time">${esc(relTime(c.created_at))}</div>
          </div>
          <div class="ugc-text">${esc(c.body_text)}</div>
          <div class="ugc-actions">
            <span class="ugc-action"><button class="ugc-like ${state.liked.has(id) ? 'liked' : ''}" data-like="${id}" type="button">♥</button><span>${Number(c.like_count||0)}</span></span>
            <span class="ugc-action"><button data-reply="${id}" type="button">Reply</button></span>
          </div>
          ${replyBox}
          ${repliesHtml}
        </div>
      </div>
    `;
  };

  /**
   * Renders the full list of comments into the DOM and attaches item-level listeners.
   */
  const renderList = () => {
    const list = root.querySelector('#ugc-list');
    if (!list) return;

    const top = sortIds(state.roots);
    list.innerHTML = top.map(id => renderComment(id)).join('');

    // Attach like button event listeners.
    list.querySelectorAll('[data-like]').forEach(btn => {
        btn.addEventListener('click', async (e) => {
            e.preventDefault();

            const id = Number(btn.dataset.like);
            if (state.liked.has(id)) return;

            const fd = new FormData();
            fd.append('comment_id', id);
            fd.append('csrf', getCsrf());

            const res = await fetch(`${apiBase}/comment-like.php`, {
              method: 'POST',
              body: fd,
              credentials: 'same-origin'
            });

            const data = await res.json().catch(() => null);
            if (!res.ok || !data || data.ok !== true) return;

            state.liked.add(id);
            btn.classList.add('liked');

            if (data.added === true) {
              const countSpan = btn.nextElementSibling;
              if (countSpan) countSpan.textContent = Number(countSpan.textContent) + 1;
            }
        });
    });

    // Attach reply button event listeners to toggle the reply form.
    list.querySelectorAll('[data-reply]').forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const id = Number(btn.dataset.reply);
        state.replyBoxOpenFor = (state.replyBoxOpenFor === id) ? null : id;
        renderList();
      });
    });
  };

  /**
   * Global listener for reply form submissions (using delegation).
   */
  root.addEventListener('submit', async (e) => {
    const form = e.target;
    if (!(form instanceof HTMLFormElement)) return;
    if (!form.matches('form[data-reply-form]')) return;

    e.preventDefault();

    const msgEl = form.querySelector('.ugc-reply-msg');
    const body = (form.querySelector('textarea[name="body_text"]')?.value || '').trim();
    const name = (form.querySelector('input[name="author_name"]')?.value || '').trim();

    resetMessage(msgEl);

    const err = validateComment(name, body);
    if (err) {
        showError(msgEl, err);
        return;
    }

    const result = await postFormAjax(form);
    if (!result.ok) {
      showError(msgEl, errorMap[result.error] || 'Unexpected error.');
      return;
    }

    // Send email notification asynchronously (non-blocking)
    notifyEmail(result.id, name, body);

    state.replyBoxOpenFor = null;
    await load();
    }, true);

    /**
     * Fetches current comments from the server and refreshes the UI.
     */
    const load = async () => {
        const url = `${apiBase}/comments.php?page_key=${encodeURIComponent(pageKey)}&_=${Date.now()}`;
        const res = await fetch(url, { credentials:'same-origin', cache:'no-store' });
        const data = await res.json();

        const raw = Array.isArray(data) ? data : (data.comments || []);
        const flat = [];
        // Walk through the server's nested structure to create a flat array for state management.
        const walk = (arr) => {
            for (const c of arr) {
            flat.push(c);
            if (Array.isArray(c.replies) && c.replies.length) walk(c.replies);
            }
        };
        walk(raw);

        buildTree(flat);
        ui();
    };

    // Initial load.
    load();

})();