'use strict';
// Top-layer popovers keep the table's row heights and column widths unchanged.
document.querySelectorAll('.st-members-panel').forEach(panel => {
  const button = document.querySelector(`[popovertarget="${panel.id}"]`);
  function positionPanel() {
    if (!panel.matches(':popover-open')) return;
    const anchor = button.getBoundingClientRect();
    const box = panel.getBoundingClientRect();
    const gap = 8;
    const top = anchor.bottom + gap + box.height <= window.innerHeight - 12
      ? anchor.bottom + gap : Math.max(12, anchor.top - box.height - gap);
    panel.style.top = `${top}px`;
    panel.style.left = `${Math.max(12, Math.min(anchor.left, window.innerWidth - box.width - 12))}px`;
  }
  panel.addEventListener('toggle', () => {
    button.setAttribute('aria-expanded', panel.matches(':popover-open') ? 'true' : 'false');
    positionPanel();
  });
  window.addEventListener('resize', positionPanel);
  window.addEventListener('scroll', positionPanel, true);
});
document.querySelectorAll('[data-print]').forEach(button => button.addEventListener('click', () => window.print()));
document.querySelectorAll('form[data-confirm]').forEach(form => form.addEventListener('submit', event => {
  if (!window.confirm(form.dataset.confirm)) event.preventDefault();
}));

const masterTable = document.getElementById('st-master-table');
if (masterTable) {
  const search = document.getElementById('st-master-search');
  const group = document.getElementById('st-master-group');
  const filter = () => {
    const needle = search.value.trim().toLocaleLowerCase('id');
    let visible = 0;
    masterTable.querySelectorAll('tbody tr').forEach(row => {
      row.hidden = !(row.dataset.search.includes(needle) && (!group.value || group.value === row.dataset.group));
      if (!row.hidden) visible++;
    });
    document.getElementById('st-master-count').textContent = `${visible} personel ditampilkan.`;
  };
  search.addEventListener('input', filter);
  group.addEventListener('change', filter);
  filter();
}

const eventForm = document.getElementById('st-event-form');
if (eventForm) {
  const roster = JSON.parse(document.getElementById('st-roster').textContent);
  const people = new Map(roster.map(person => [String(person.id), person]));
  const team = document.getElementById('st-team');
  const error = document.getElementById('st-team-error');
  let nextIndex = team.querySelectorAll('.st-team-row').length;
  let nextList = 0;
  const selectedIds = () => [...team.querySelectorAll('.st-person-id')].map(input => input.value).filter(Boolean);
  function prepareRow(row) {
    const input = row.querySelector('.st-person-search');
    const hidden = row.querySelector('.st-person-id');
    const info = row.querySelector('.st-person-info');
    const box = document.createElement('div');
    box.className = 'st-suggestions';
    box.id = `st-options-${nextList++}`;
    box.role = 'listbox';
    box.hidden = true;
    input.parentElement.append(box);
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', box.id);
    input.setAttribute('aria-expanded', 'false');
    let matches = [];
    let active = -1;
    const validity = () => {
      const person = people.get(hidden.value);
      input.setCustomValidity(person && input.value === person.nama ? '' : 'Pilih nama dari daftar autocomplete.');
    };
    const close = () => {
      box.hidden = true;
      active = -1;
      input.setAttribute('aria-expanded', 'false');
      input.removeAttribute('aria-activedescendant');
    };
    const choose = person => {
      hidden.value = String(person.id);
      input.value = person.nama;
      info.textContent = (person.kelompok === 'referensi' ? 'Di luar jurusan' : (person.nip || 'NIP belum diisi')) + (person.aktif ? '' : ' · Nonaktif');
      validity();
      close();
      error.textContent = '';
    };
    const show = () => {
      const needle = input.value.trim().toLocaleLowerCase('id');
      const used = selectedIds().filter(id => id !== hidden.value);
      matches = roster.filter(person => (person.aktif || String(person.id) === hidden.value) && !used.includes(String(person.id)) && (!needle || `${person.nama} ${person.nip || ''}`.toLocaleLowerCase('id').includes(needle))).slice(0, 40);
      box.replaceChildren();
      active = -1;
      input.removeAttribute('aria-activedescendant');
      if (!matches.length) {
        const empty = document.createElement('div');
        empty.className = 'st-no-match';
        empty.textContent = 'Tidak ada nama yang sesuai. Periksa data dosen atau tambahkan personel terlebih dahulu.';
        box.append(empty);
      }
      matches.forEach((person, index) => {
        const option = document.createElement('button');
        option.type = 'button';
        option.className = 'st-option';
        option.id = `${box.id}-${index}`;
        option.setAttribute('role', 'option');
        option.setAttribute('aria-selected', 'false');
        option.tabIndex = -1;
        const name = document.createElement('span');
        name.textContent = person.nama;
        const meta = document.createElement('small');
        meta.textContent = person.kelompok === 'referensi' ? 'Di luar jurusan' : (person.nip || 'NIP belum diisi');
        option.append(name, meta);
        option.addEventListener('mousedown', event => event.preventDefault());
        option.addEventListener('click', () => { choose(person); input.focus(); close(); });
        box.append(option);
      });
      box.hidden = false;
      input.setAttribute('aria-expanded', 'true');
    };
    input.addEventListener('focus', show);
    input.addEventListener('input', () => {
      hidden.value = '';
      info.textContent = '';
      validity();
      show();
    });
    input.addEventListener('keydown', event => {
      if (event.key === 'Escape') { close(); return; }
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
        event.preventDefault();
        if (box.hidden) show();
        if (!matches.length) return;
        active = (active + (event.key === 'ArrowDown' ? 1 : -1) + matches.length) % matches.length;
        [...box.querySelectorAll('.st-option')].forEach((option, i) => {
          option.classList.toggle('active', i === active);
          option.setAttribute('aria-selected', i === active ? 'true' : 'false');
          if (i === active) { input.setAttribute('aria-activedescendant', option.id); option.scrollIntoView({block:'nearest'}); }
        });
      } else if (event.key === 'Enter' && !box.hidden && active >= 0) {
        event.preventDefault();
        choose(matches[active]);
      } else if (event.key === 'Tab') close();
    });
    input.addEventListener('blur', () => { close(); validity(); });
    row.querySelector('.st-remove').addEventListener('click', () => { row.remove(); error.textContent=''; });
    validity();
  }
  team.querySelectorAll('.st-team-row').forEach(prepareRow);
  document.getElementById('st-add-member').addEventListener('click', () => {
    if (team.children.length >= 200) { error.textContent='Maksimal 200 personel per kegiatan.'; return; }
    const index = nextIndex++;
    const row = document.createElement('div');
    row.className = 'st-team-row';
    row.innerHTML = `<div class="st-autocomplete"><label class="st-mobile-label">Nama personel</label><input type="hidden" class="st-person-id" name="tim[${index}][personel_id]"><input type="text" class="st-person-search" placeholder="Cari nama atau NIP…" aria-label="Nama personel ${index+1}" autocomplete="off" required><small class="st-person-info"></small></div><label><span class="st-mobile-label">Peran</span><select class="st-role" name="tim[${index}][peran]"><option value="ketua">Ketua</option><option value="sekretaris">Sekretaris</option><option value="anggota" selected>Anggota</option></select></label><div><label class="st-mobile-label">Divisi &amp; tanggung jawab</label><input type="text" name="tim[${index}][divisi]" maxlength="200" placeholder="Divisi" aria-label="Divisi personel ${index+1}"><textarea name="tim[${index}][tanggung_jawab]" maxlength="1000" rows="2" placeholder="Tanggung jawab" aria-label="Tanggung jawab personel ${index+1}"></textarea></div><button type="button" class="btn st-remove" aria-label="Hapus baris personel ${index+1}">Hapus</button>`;
    team.append(row);
    prepareRow(row);
    row.querySelector('.st-person-search').focus();
  });
  const precision = document.getElementById('st-precision');
  const start = document.getElementById('st-start');
  const end = document.getElementById('st-end');
  function timeBounds() {
    const type = precision.value === 'bulan' ? 'month' : 'date';
    start.min = type === 'month' ? '1900-01' : '1900-01-01';
    start.max = type === 'month' ? '2200-12' : '2200-12-31';
    end.min = start.value || start.min;
    end.max = start.max;
  }
  precision.addEventListener('change', () => {
    [start,end].forEach((input,index) => {
      let value = input.value;
      if (precision.value === 'bulan') value = value.slice(0,7);
      else if (/^\d{4}-\d{2}$/.test(value)) {
        const [year,month] = value.split('-').map(Number);
        value += '-'+String(index === 0 ? 1 : new Date(year,month,0).getDate()).padStart(2,'0');
      }
      input.type = precision.value === 'bulan' ? 'month' : 'date';
      input.value = value;
    });
    timeBounds();
  });
  start.addEventListener('change', timeBounds);
  timeBounds();
  const status = document.getElementById('st-status');
  const number = document.getElementById('st-number');
  const updateNumber = () => { number.required = status.value === 'terbit'; };
  status.addEventListener('change',updateNumber);
  updateNumber();
  eventForm.addEventListener('submit', event => {
    error.textContent = '';
    const rows = [...team.querySelectorAll('.st-team-row')];
    const counts = {ketua:0,sekretaris:0,anggota:0};
    const ids = selectedIds();
    rows.forEach(row => { counts[row.querySelector('.st-role').value]++; });
    if (counts.ketua !== 1 || counts.sekretaris !== 1 || counts.anggota < 1) error.textContent='Isi tepat satu ketua, satu sekretaris, dan minimal satu anggota.';
    else if (ids.length !== rows.length) error.textContent='Pilih semua nama dari autocomplete.';
    else if (new Set(ids).size !== ids.length) error.textContent='Satu orang hanya boleh memiliki satu peran dalam kegiatan yang sama.';
    if (error.textContent) { event.preventDefault(); error.scrollIntoView({block:'center'}); }
  });
}
