{{-- ═══════════════════════════════════════════════
     Shared styles for Create / Edit Email Template
═══════════════════════════════════════════════ --}}
<style>
/* ── Container ── */
.dash-container { padding: 24px; }

/* ── Info Banner (edit page only) ── */
.et-banner {
    display: flex; align-items: center; gap: 16px;
    background: linear-gradient(135deg, #005757, #163f7a);
    border-radius: 18px; padding: 18px 24px; color: #fff; flex-wrap: wrap;
}
.et-banner-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: rgba(255,255,255,.15);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; flex-shrink: 0;
}
.et-banner-info  { flex: 1; min-width: 0; }
.et-banner-title { font-size: 1rem; font-weight: 800; }
.et-banner-sub   { font-size: 0.78rem; opacity: .80; margin-top: 4px; }
.et-banner-pill  {
    display: inline-flex; align-items: center; gap: 6px;
    border-radius: 20px; padding: 6px 14px; font-size: 0.78rem; font-weight: 700;
}
.et-pill-active   { background: rgba(52,168,83,.2);  border: 1px solid rgba(52,168,83,.4);  color: #b7f5c8; }
.et-pill-inactive { background: rgba(234,67,53,.2); border: 1px solid rgba(234,67,53,.4); color: #ffd0cc; }

/* ── Form Card ── */
.et-form-card   { border-radius: 18px; border: 1px solid #e8eaed; overflow: hidden; }

/* ── Card Header ── */
.et-form-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 20px 28px;
    background: linear-gradient(135deg, #005757, #163f7a);
}
.et-form-header-title { font-size: 1rem; font-weight: 700; color: #fff; margin: 0; }
.et-form-header-sub   { font-size: 0.74rem; color: rgba(255,255,255,.72); margin: 4px 0 0; }
.et-back-btn {
    display: inline-flex; align-items: center; gap: 5px;
    background: rgba(255,255,255,.15); border: 1px solid rgba(255,255,255,.3);
    color: #fff; border-radius: 10px; padding: 7px 14px;
    font-size: 0.82rem; font-weight: 600; text-decoration: none; transition: background .15s;
}
.et-back-btn:hover { background: rgba(255,255,255,.25); color: #fff; }

/* ── Form Body ── */
.et-form-body { padding: 28px; background: #f4fbfb; }

/* ── Section Title ── */
.et-section-title {
    font-size: 0.72rem; font-weight: 700; color: #163f7a;
    text-transform: uppercase; letter-spacing: .07em;
    margin: 24px 0 14px; padding-bottom: 5px;
    border-bottom: 1.5px solid rgba(22, 63, 122,.12);
}
.et-section-title:first-child { margin-top: 0; }

/* ── Field ── */
.et-field { display: flex; flex-direction: column; }
.et-field label { font-size: 0.78rem; color: #5f6368; margin-bottom: 5px; font-weight: 500; }
.et-field .req  { color: #ea4335; }

/* ── Input Box ── */
.et-input-box {
    display: flex; align-items: center;
    border: 1.5px solid #d1d5db; border-radius: 10px;
    background: #fff; overflow: hidden;
    transition: border-color .15s, box-shadow .15s; height: 44px;
}
.et-input-box:focus-within {
    border-color: #163f7a;
    box-shadow: 0 0 0 3px rgba(22, 63, 122,.08);
}
.et-disabled { background: #f8f9fa; }
.et-disabled .et-icon { opacity: .5; }
.et-icon {
    display: flex; align-items: center; justify-content: center;
    width: 40px; height: 100%; flex-shrink: 0;
    color: #163f7a; font-size: 1.05rem;
    border-right: 1.5px solid #e8eaed; background: #f8fdfd;
}
.et-input-box input,
.et-input-box select {
    flex: 1; border: none !important; outline: none !important;
    box-shadow: none !important; background: transparent;
    font-size: 0.875rem; color: #202124; padding: 0 12px; height: 100%;
    appearance: none; -webkit-appearance: none;
}
.et-input-box input::placeholder { color: #9aa0a6; }
.et-input-box select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='%235f6368'%3E%3Cpath d='M7 10l5 5 5-5z'/%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 10px center; padding-right: 28px;
}

/* ── Textarea Box ── */
.et-textarea-box {
    border: 1.5px solid #d1d5db; border-radius: 10px; background: #fff; overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
}
.et-textarea-box:focus-within {
    border-color: #163f7a; box-shadow: 0 0 0 3px rgba(22, 63, 122,.08);
}
.et-textarea-box textarea {
    width: 100%; border: none !important; outline: none !important;
    box-shadow: none !important; background: transparent;
    font-size: 0.875rem; color: #202124; padding: 12px 14px;
    font-family: 'Courier New', monospace; resize: vertical; min-height: 220px;
}
.et-textarea-box textarea::placeholder { color: #9aa0a6; font-family: inherit; }

/* ── Variable Bar ── */
.et-variables-bar {
    display: flex; align-items: center; flex-wrap: wrap; gap: 7px;
    background: rgba(22, 63, 122,0.04); border: 1.5px solid rgba(22, 63, 122,0.15);
    border-radius: 12px; padding: 12px 16px;
}
.et-var-label {
    font-size: 0.75rem; font-weight: 700; color: #163f7a;
    display: flex; align-items: center; gap: 4px; margin-right: 4px;
}
.et-var-hint { font-size: 0.70rem; color: #80868b; margin-right: 6px; }
.et-var-chip {
    display: inline-flex; align-items: center;
    background: rgba(26,115,232,0.08); border: 1px solid rgba(26,115,232,0.2);
    color: #1a73e8; border-radius: 6px; padding: 4px 10px;
    font-size: 0.72rem; font-weight: 700; font-family: 'Courier New', monospace;
    cursor: pointer; transition: all .15s;
}
.et-var-chip:hover { background: rgba(26,115,232,0.15); border-color: rgba(26,115,232,0.4); transform: translateY(-1px); }

/* ── Reminder Row ── */
.et-reminder-row { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 10px; }
.et-reminder-item { display: flex; align-items: center; gap: 8px; }
.et-remove-day {
    width: 34px; height: 34px; border-radius: 8px; border: 1.5px solid rgba(234,67,53,.2);
    background: rgba(234,67,53,.06); color: #ea4335; font-size: 1rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: all .15s;
}
.et-remove-day:hover { background: #ea4335; color: #fff; }
.et-add-day-btn {
    display: inline-flex; align-items: center; gap: 5px;
    border: 1.5px dashed #163f7a; background: rgba(22, 63, 122,0.04); color: #163f7a;
    border-radius: 10px; padding: 7px 16px; font-size: 0.82rem; font-weight: 600;
    cursor: pointer; transition: all .15s; margin-top: 2px;
}
.et-add-day-btn:hover { background: rgba(22, 63, 122,.1); }

/* ── Footer ── */
.et-form-footer {
    display: flex; justify-content: flex-end; gap: 8px;
    padding-top: 20px; border-top: 1px solid #e8eaed; flex-wrap: wrap;
}
.et-btn-cancel {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.85rem; padding: 9px 20px; border-radius: 10px;
    border: 1.5px solid #d1d5db; background: #fff; color: #5f6368;
    cursor: pointer; text-decoration: none; transition: background .15s;
}
.et-btn-cancel:hover { background: #f5f5f5; color: #444; }
.et-btn-save {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 0.85rem; font-weight: 700; padding: 9px 24px; border-radius: 10px;
    border: none; background: #163f7a; color: #fff; cursor: pointer; transition: background .15s;
}
.et-btn-save:hover { background: #004e4e; }

@media (max-width: 768px) {
    .et-form-body   { padding: 16px; }
    .et-form-header { padding: 16px 18px; flex-direction: column; align-items: flex-start; gap: 10px; }
    .et-banner      { flex-direction: column; }
}
</style>
