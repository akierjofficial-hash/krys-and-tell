(() => {
  const root = document.getElementById('staffRecordAssistant');
  if (!root) return;
  const $ = selector => root.querySelector(selector);
  const launcher = $('.record-assistant__launcher');
  const panel = $('.record-assistant__panel');
  const close = $('.record-assistant__close');
  const searchWrap = $('.record-assistant__search-wrap');
  const search = $('#recordAssistantSearch');
  const candidates = $('.record-assistant__candidates');
  const searchStatus = $('.record-assistant__search-status');
  const selected = $('.record-assistant__selected');
  const switchButton = $('.record-assistant__switch');
  const clearButton = $('.record-assistant__clear');
  const suggestions = $('.record-assistant__suggestions');
  const clinicSuggestions = $('.record-assistant__clinic-suggestions');
  const empty = $('.record-assistant__empty');
  const body = $('.record-assistant__body');
  const messages = $('.record-assistant__messages');
  const dates = $('.record-assistant__dates');
  const form = $('.record-assistant__form');
  const status = $('.record-assistant__status');
  const questionInput = form.elements.question;
  const dateFrom = form.elements.date_from;
  const dateTo = form.elements.date_to;
  const submit = form.querySelector('button');
  const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
  let patient = root.dataset.patientId ? {id: Number(root.dataset.patientId), name: root.dataset.patientName} : null;
  let searchTimer;
  let searchSerial = 0;
  let asking = false;
  let clinicMode = false;

  function setSearchOpen(open) {
    searchWrap.hidden = !open;
    switchButton.setAttribute('aria-expanded', String(open));
    if (open && !panel.hidden) search.focus();
  }
  function renderPatient() {
    selected.textContent = patient ? `${patient.name} · Patient #${patient.id}${patient.birthdate ? ` · Born ${patient.birthdate}` : ''}` : 'All clinic records';
    selected.classList.toggle('record-assistant__selected--empty', !patient);
    clearButton.hidden = !patient;
    suggestions.hidden = !patient;
    questionInput.placeholder = patient ? `Ask about ${patient.name} or the clinic` : 'Ask about clinic records';
  }
  function reply(message, links = [], isError = false) {
    const box = document.createElement('div');
    box.className = 'record-assistant__response' + (isError ? ' record-assistant__response--error' : '');
    const text = document.createElement('p');
    text.className = 'record-assistant__reply';
    text.textContent = message;
    box.append(text);
    if (links.length) {
      const list = document.createElement('div');
      list.className = 'record-assistant__links';
      links.forEach(item => {
        const anchor = document.createElement('a');
        anchor.href = item.url;
        anchor.textContent = item.label;
        list.append(anchor);
      });
      box.append(list);
    }
    messages.append(box);
    body.scrollTop = body.scrollHeight;
  }
  function resetConversation() {
    messages.replaceChildren();
    empty.hidden = false;
    questionInput.value = '';
    dates.hidden = true;
    clinicMode = false;
  }
  function choose(nextPatient) {
    ++searchSerial;
    clearTimeout(searchTimer);
    patient = nextPatient;
    search.value = nextPatient.name;
    candidates.replaceChildren();
    searchStatus.textContent = '';
    setSearchOpen(false);
    renderPatient();
    resetConversation();
    questionInput.focus();
  }
  function syncViewport() {
    if (!panel.hidden && window.matchMedia('(max-width: 600px)').matches && window.visualViewport) {
      panel.style.height = `min(100dvh, ${window.visualViewport.height}px)`;
      panel.style.top = `${window.visualViewport.offsetTop}px`;
    } else {
      panel.style.removeProperty('height');
      panel.style.removeProperty('top');
    }
  }
  function toggle(open) {
    panel.hidden = !open;
    root.classList.toggle('record-assistant--open', open);
    launcher.hidden = open;
    launcher.setAttribute('aria-expanded', String(open));
    syncViewport();
    if (open) questionInput.focus();
    else launcher.focus();
  }
  function needsVisitDates(question) {
    return /\bvisits?\b/i.test(question) && /\b(how many|count|number|date range|between)\b/i.test(question)
      && (!patient || clinicMode || /\b(all|clinic|across|overall)\b/i.test(question));
  }
  function updateDateFilters() {
    dates.hidden = !needsVisitDates(questionInput.value.trim());
  }
  renderPatient();
  setSearchOpen(false);
  window.visualViewport?.addEventListener('resize', syncViewport);
  window.visualViewport?.addEventListener('scroll', syncViewport);
  window.addEventListener('resize', syncViewport);
  launcher.addEventListener('click', () => toggle(panel.hidden));
  close.addEventListener('click', () => toggle(false));
  panel.addEventListener('keydown', event => {
    if (event.key === 'Escape') { event.preventDefault(); toggle(false); }
    if (event.key === 'Tab') {
      const focusable = [...panel.querySelectorAll('button:not(:disabled),input:not(:disabled),a[href]')]
        .filter(element => element.getClientRects().length);
      if (!focusable.length) return;
      if (event.shiftKey && document.activeElement === focusable[0]) { event.preventDefault(); focusable.at(-1).focus(); }
      else if (!event.shiftKey && document.activeElement === focusable.at(-1)) { event.preventDefault(); focusable[0].focus(); }
    }
  });
  switchButton.addEventListener('click', () => {
    const open = searchWrap.hidden;
    setSearchOpen(open);
    if (open) {
      search.select();
      searchStatus.textContent = patient ? 'Confirm the patient ID and birthdate before switching.' : 'Search by name, then confirm ID and birthdate.';
    } else questionInput.focus();
  });
  clearButton.addEventListener('click', () => {
    ++searchSerial;
    clearTimeout(searchTimer);
    patient = null;
    search.value = '';
    candidates.replaceChildren();
    searchStatus.textContent = '';
    status.textContent = '';
    renderPatient();
    setSearchOpen(false);
    resetConversation();
    questionInput.focus();
  });
  search.addEventListener('input', () => {
    candidates.replaceChildren();
    clearTimeout(searchTimer);
    const serial = ++searchSerial;
    const q = search.value.trim();
    if (q.length < 2) { searchStatus.textContent = q ? 'Type at least 2 letters.' : ''; return; }
    searchStatus.textContent = 'Searching patients…';
    searchTimer = setTimeout(async () => {
      try {
        const response = await fetch(root.dataset.searchUrl, {method:'POST',
          headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},
          body:JSON.stringify({q}), cache:'no-store'});
        const data = await response.json().catch(() => ({}));
        if (serial !== searchSerial) return;
        if (response.status === 401 || response.status === 403 || response.status === 419) throw new Error('Your staff session is unavailable. Sign in again to search patient records.');
        if (response.status === 429) throw new Error('Patient search limit reached. Wait a minute, then try again.');
        if (!response.ok) throw new Error(data.message || 'Patient search is unavailable. Check the clinic database connection.');
        if (!data.patients?.length) { searchStatus.textContent = 'No matching patients. Try another name.'; return; }
        searchStatus.textContent = data.patients.length >= 10 ? 'Showing the first 10 matches. Refine the name, then confirm ID and birthdate.'
          : data.patients.length > 1 ? 'Several matches found. Confirm the patient ID and birthdate.' : 'Select this patient to continue.';
        data.patients.forEach(candidate => {
          const button = document.createElement('button');
          button.type = 'button';
          button.className = 'record-assistant__candidate';
          button.textContent = `${candidate.name} · #${candidate.id}${candidate.birthdate ? ` · Born ${candidate.birthdate}` : ''}`;
          button.addEventListener('click', () => choose(candidate));
          candidates.append(button);
        });
      } catch (error) {
        if (serial === searchSerial) searchStatus.textContent = error.message || 'Patient search is unavailable.';
      }
    }, 250);
  });
  suggestions.querySelectorAll('button[data-patient-question]').forEach(button => {
    button.addEventListener('click', () => {
      clinicMode = false;
      questionInput.value = button.dataset.patientQuestion;
      updateDateFilters();
      form.requestSubmit();
    });
  });
  clinicSuggestions.querySelectorAll('button[data-clinic-question]').forEach(button => {
    button.addEventListener('click', () => {
      clinicMode = true;
      questionInput.value = button.dataset.clinicQuestion;
      updateDateFilters();
      form.requestSubmit();
    });
  });
  questionInput.addEventListener('input', () => { clinicMode = false; updateDateFilters(); });
  form.addEventListener('submit', async event => {
    event.preventDefault();
    if (asking) return;
    const question = questionInput.value.trim();
    if (!question) return;
    if (needsVisitDates(question) && (!dateFrom.value || !dateTo.value)) {
      dates.hidden = false;
      status.textContent = 'Choose both visit count dates first.';
      (!dateFrom.value ? dateFrom : dateTo).focus();
      return;
    }
    const requestPatient = patient;
    const requestClinic = clinicMode || !patient;
    clinicMode = false;
    const bubble = document.createElement('p');
    bubble.className = 'record-assistant__question';
    bubble.textContent = question;
    empty.hidden = true;
    messages.append(bubble);
    body.scrollTop = body.scrollHeight;
    questionInput.value = '';
    dates.hidden = true;
    asking = true;
    submit.disabled = true;
    root.querySelectorAll('[data-patient-question],[data-clinic-question]').forEach(button => { button.disabled = true; });
    status.textContent = 'Checking current records…';
    status.classList.add('record-assistant__status--loading');
    try {
      const response = await fetch(root.dataset.askUrl, {method:'POST',headers:{Accept:'application/json','Content-Type':'application/json','X-CSRF-TOKEN':csrf},body:JSON.stringify({scope:requestClinic ? 'clinic' : 'patient',patient_id:requestClinic ? null : requestPatient?.id || null,question,date_from:dateFrom.value || null,date_to:dateTo.value || null}),cache:'no-store'});
      const data = await response.json().catch(() => ({}));
      if (!requestClinic && requestPatient && patient?.id !== requestPatient.id) return;
      if (!response.ok) {
        if (response.status === 401 || response.status === 403 || response.status === 419) throw new Error('Your staff session is unavailable. Sign in again to use the assistant.');
        if (response.status === 429) throw new Error('Assistant request limit reached. Wait a minute, then try again. You can still open the linked staff records directly.');
        throw new Error(data.message || (response.status >= 500 ? 'Records are temporarily unavailable. Check the clinic database connection.' : 'Unable to answer this question.'));
      }
      reply(data.message || 'No answer was returned.', data.links || []);
    } catch (error) {
      if (requestClinic || !requestPatient || patient?.id === requestPatient.id) reply(error.message || 'Records are temporarily unavailable. Please try again.', [], true);
    } finally {
      asking = false;
      submit.disabled = false;
      root.querySelectorAll('[data-patient-question],[data-clinic-question]').forEach(button => { button.disabled = false; });
      status.textContent = '';
      status.classList.remove('record-assistant__status--loading');
      if (!panel.hidden) questionInput.focus();
    }
  });
})();
