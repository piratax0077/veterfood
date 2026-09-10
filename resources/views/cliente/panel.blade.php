@extends('layouts.app')

@section('title', 'Mi cuenta')

@section('content')
<style>
    .client-page{max-width:1480px;margin:0 auto}
    .client-hero{margin-bottom:18px}
    .client-hero h1{font-size:34px;margin:0 0 2px;color:#06152f}
    .client-hero p{margin:0}
    /* Secciones de cuenta: perfil, contrasena, compras y tarjetas */
    .client-page [hidden]{display:none!important}
    /* Encabezado unico de seccion: titulo + subtitulo a la izquierda, accion principal a la derecha */
    .section-head,.form-toggle-row{display:flex;justify-content:space-between;align-items:center;gap:16px;margin:0 0 18px}
    .section-head h2,.form-toggle-row h2{margin:0 0 4px;color:#06152f;font-size:24px;font-weight:800;line-height:1.2}
    .section-head p,.form-toggle-row p{margin:0;font-size:15px;line-height:1.45}
    /* Boton unico de accion de seccion (Editar, Agregar, Nuevo) */
    .btn-edit,.btn-form-toggle{display:inline-flex;align-items:center;justify-content:center;gap:8px;flex:0 0 auto;min-width:0;min-height:42px;padding:10px 20px;border:0;border-radius:999px;background:linear-gradient(135deg,#087f67,#10a37f);color:#fff;font-family:inherit;font-size:14.5px;font-weight:800;line-height:1.2;white-space:nowrap;box-shadow:0 5px 13px rgba(8,127,103,.22);cursor:pointer;transition:filter .15s ease,box-shadow .15s ease,background .15s ease,color .15s ease}
    .btn-edit:hover,.btn-form-toggle:hover{transform:none;filter:brightness(1.06);box-shadow:0 8px 18px rgba(8,127,103,.28)}
    .btn-edit:focus-visible,.btn-form-toggle:focus-visible{outline:2px solid #10a37f;outline-offset:3px}
    .btn-edit .isdi,.btn-form-toggle .isdi{margin:0;font-size:17px;transition:transform .18s ease}
    .btn-form-toggle[aria-expanded="true"],.btn-form-toggle[aria-expanded="true"]:hover{background:#fff;color:#03715b;box-shadow:inset 0 0 0 1.5px #03715b;filter:none}
    .btn-form-toggle[aria-expanded="true"] .isdi{transform:rotate(45deg)}
    .form-title{margin:0 0 14px;color:#06152f;font-size:18px;font-weight:800}
    .item-row.between{display:flex;justify-content:space-between;align-items:center;gap:14px}
    .item-main{display:flex;align-items:center;gap:14px;min-width:0}
    .item-main .item-thumb{width:64px;height:64px;border-radius:10px}
    .item-main .item-thumb .isdi{font-size:28px;color:#10a37f}
    .item-main .item-thumb.item-thumb-redondo{width:56px;height:56px;border-radius:50%}
    .item-main .item-thumb.item-thumb-redondo .isdi{font-size:24px}
    .item-row .actions{flex:0 0 auto;gap:4px}
    .editable-form fieldset{min-width:0;margin:0;padding:0;border:0}
    .client-page .editable-form fieldset:disabled .form-control.form-control-sm{background:#f5f8fa!important;border-color:#e2e8f0!important;color:#33415c!important;-webkit-text-fill-color:#33415c;opacity:1;cursor:default}
    .editable-form:not(.is-editing) .solo-edicion,.editable-form.is-editing .solo-vista{display:none}
    .edit-actions{display:none;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0}
    .editable-form.is-editing .edit-actions{display:flex}
    .edit-actions .btn{min-height:42px}
    .client-page .compact-form .field-hint{display:block;margin-top:5px;color:#64748b;font-size:12px;font-weight:600}
    .client-page .compact-form .field-error{display:block;margin-top:5px;color:#b42318;font-size:12.5px;font-weight:700}
    .client-page .has-error .form-control.form-control-sm{border-color:#f04438!important}
    .phone-group{display:flex;align-items:stretch}
    .phone-prefix{display:inline-flex;align-items:center;gap:7px;flex:0 0 auto;padding:0 11px;border:1px solid #cbd5e1;border-right:0;border-radius:7px 0 0 7px;background:#f1f5f9;color:#172033;font-size:14px;font-weight:800}
    .phone-prefix svg{width:22px;height:15px;border-radius:2px;box-shadow:0 0 0 1px rgba(15,23,42,.14)}
    .client-page .phone-group .form-control.form-control-sm{flex:1 1 auto;min-width:0;border-radius:0 7px 7px 0!important}
    fieldset:disabled .phone-prefix{border-color:#e2e8f0;background:#eef2f6}
    .password-form{max-width:620px}
    .pass-field{position:relative}
    .client-page .pass-field .form-control.form-control-sm{padding-right:46px!important}
    .pass-eye{position:absolute;top:50%;right:4px;display:inline-flex;align-items:center;justify-content:center;width:36px!important;min-width:0;height:30px;min-height:0;padding:0;border-radius:6px;background:transparent;box-shadow:none;color:#64748b;transform:translateY(-50%)}
    .pass-eye:hover{background:#eef2f6;color:#03715b;box-shadow:none;transform:translateY(-50%)}
    .pass-eye svg{width:19px;height:19px}
    .pass-eye .eye-off,.pass-eye[aria-pressed="true"] .eye-on{display:none}
    .pass-eye[aria-pressed="true"] .eye-off{display:block}
    .pass-rules{display:grid;gap:4px;margin:4px 0 0;padding:0;list-style:none;color:#64748b;font-size:13px;font-weight:700}
    .pass-rules li{display:flex;align-items:center;gap:7px}
    .pass-rules li:before{content:"";width:8px;height:8px;border-radius:50%;background:#cbd5e1;transition:background .15s ease}
    .pass-rules li.ok{color:#03715b}
    .pass-rules li.ok:before{background:#10a37f}
    .info-note{display:flex;gap:9px;align-items:flex-start;margin:0 0 14px;padding:10px 12px;border-radius:10px;background:#f1f8f6;color:#33544c;font-size:13.5px;line-height:1.4}
    .info-note .isdi{margin-top:1px;color:#03715b;font-size:17px}
    .orders-toolbar{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px}
    .order-filter{min-width:0;min-height:34px;padding:6px 14px;border-radius:999px;background:#fff;color:#294b54;box-shadow:inset 0 0 0 1px #cbd5e1;font-size:13px;font-weight:800}
    .order-filter:hover{transform:none;background:#f1f5f9;box-shadow:inset 0 0 0 1px #94a3b8}
    .order-filter.active,.order-filter.active:hover{background:#03715b;color:#fff;box-shadow:none}
    .order-list{display:grid;gap:16px}
    .order-card{overflow:hidden;background:#fff;border-radius:13px;box-shadow:0 2px 6px rgba(18,63,75,.05),0 16px 40px rgba(18,63,75,.13)}
    .order-head{display:flex;flex-wrap:wrap;align-items:center;gap:10px 30px;padding:14px 20px;background:#f6fafb;border-bottom:1px solid #e2e8f0}
    .order-meta span{display:block;color:#64748b;font-size:11.5px;font-weight:800;letter-spacing:.04em;text-transform:uppercase}
    .order-meta strong{color:#06152f;font-size:14.5px}
    .order-head .order-status{margin-left:auto}
    .order-status{display:inline-flex;align-items:center;gap:7px;border-radius:999px;padding:5px 12px;font-size:12.5px;font-weight:900;white-space:nowrap}
    .order-status:before{content:"";width:7px;height:7px;border-radius:50%;background:currentColor}
    .status-curso{background:#fff4e5;color:#b45309}
    .status-entregado{background:#e7f5f0;color:#03715b}
    .status-cancelado{background:#fef3f2;color:#b42318}
    .order-body{display:grid;grid-template-columns:minmax(0,1fr) 230px;gap:18px;padding:18px 20px}
    .order-note{margin:0 0 12px;color:#06152f;font-weight:800}
    .order-note span{color:#64748b;font-weight:600}
    .order-items{display:grid;gap:12px;margin:0;padding:0;list-style:none}
    .order-item{display:flex;align-items:center;gap:14px}
    .order-item .item-thumb{width:64px;height:64px;border-radius:10px}
    .order-item-info{flex:1 1 auto;min-width:0}
    .order-item-info strong{display:block;color:#06152f;line-height:1.3}
    .order-item-info span{color:#64748b;font-size:13px}
    .order-item-price{color:#06152f;font-weight:800;white-space:nowrap}
    .order-side{display:flex;flex-direction:column;justify-content:center;gap:8px;padding-left:18px;border-left:1px solid #eef2f6}
    .order-side form{margin:0}
    .order-side .btn{width:100%;min-width:0;min-height:40px;padding:9px 14px;font-size:14px}
    .order-tag{display:inline-flex;margin-left:6px;border-radius:999px;padding:2px 8px;background:#ede9fe;color:#5b21b6;font-size:11px;font-weight:900;vertical-align:2px}
    .order-detail{border-top:1px solid #e2e8f0}
    .order-detail summary{display:flex;align-items:center;gap:6px;padding:12px 20px;color:#03715b;font-size:14px;font-weight:800;cursor:pointer;list-style:none}
    .order-detail summary::-webkit-details-marker{display:none}
    .order-detail summary:after{content:"";width:7px;height:7px;margin-left:2px;border-right:2px solid currentColor;border-bottom:2px solid currentColor;transform:rotate(45deg) translateY(-2px);transition:transform .15s ease}
    .order-detail[open] summary:after{transform:rotate(-135deg) translateY(-1px)}
    .order-detail-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:18px;padding:0 20px 18px}
    .order-detail-grid h3{margin:0 0 6px;color:#64748b;font-size:12px;letter-spacing:.04em;text-transform:uppercase}
    .order-detail-grid p{margin:0;color:#172033;font-size:14px;line-height:1.45}
    .order-totals{display:grid;gap:4px}
    .order-totals div{display:flex;justify-content:space-between;gap:12px;font-size:14px}
    .order-totals .order-total{margin-top:2px;padding-top:6px;border-top:1px solid #e2e8f0;font-size:16px;font-weight:900}
    .saved-cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:18px}
    .saved-card{display:flex;flex-direction:column;gap:10px}
    .card-visual{position:relative;display:flex;flex-direction:column;justify-content:space-between;aspect-ratio:1.586;overflow:hidden;padding:18px 20px;border-radius:14px;background:linear-gradient(135deg,#12313b,#3b6470);color:#fff;box-shadow:0 10px 24px rgba(18,63,75,.22)}
    .card-visual:after{content:"";position:absolute;top:-70px;right:-50px;width:190px;height:190px;border-radius:50%;background:rgba(255,255,255,.08);pointer-events:none}
    .card-visual.marca-visa{background:linear-gradient(135deg,#1a1f71,#3552c4)}
    .card-visual.marca-mastercard{background:linear-gradient(135deg,#232526,#6b3a17)}
    .card-visual.marca-american-express{background:linear-gradient(135deg,#006fcf,#3aa6ec)}
    .card-visual.marca-diners-club{background:linear-gradient(135deg,#0b3a5b,#4d7fa3)}
    .card-visual.marca-maestro{background:linear-gradient(135deg,#0e4d92,#c8102e)}
    .card-visual.is-vencida{filter:grayscale(.85)}
    .card-top{display:flex;justify-content:space-between;align-items:flex-start;gap:10px}
    .card-brand{font-size:17px;font-weight:900;letter-spacing:.02em}
    .card-kind{display:block;margin-top:1px;font-size:11px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;opacity:.8}
    .card-chip{width:38px;height:28px;border-radius:6px;background:linear-gradient(135deg,#f5d77b,#c9a444)}
    .card-number{font-size:clamp(16px,1.6vw,19px);font-weight:800;letter-spacing:.14em;font-variant-numeric:tabular-nums;white-space:nowrap}
    .card-bottom{display:flex;justify-content:space-between;align-items:flex-end;gap:10px}
    .card-bottom span{display:block;font-size:10px;letter-spacing:.08em;text-transform:uppercase;opacity:.75}
    .card-bottom strong{display:block;max-width:190px;overflow:hidden;font-size:13.5px;font-weight:800;letter-spacing:.03em;text-overflow:ellipsis;white-space:nowrap}
    .card-meta{display:flex;flex-wrap:wrap;align-items:center;gap:6px;min-height:26px}
    .card-meta .muted{font-size:13px}
    .card-badge{display:inline-flex;border-radius:999px;padding:3px 9px;font-size:11.5px;font-weight:900}
    .badge-default{background:#e7f5f0;color:#03715b}
    .badge-vencida{background:#fef3f2;color:#b42318}
    .card-actions{display:flex;flex-wrap:wrap;gap:4px}
    .card-actions form{margin:0}
    .link-action{display:inline-flex;align-items:center;gap:6px;width:auto!important;min-width:0;min-height:34px;padding:6px 12px;border-radius:999px;background:transparent;box-shadow:none;color:#03715b;font-size:13.5px;font-weight:800;white-space:nowrap}
    .link-action .isdi{margin:0;font-size:15px}
    .link-action:hover{transform:none;background:#e7f5f0;box-shadow:none}
    .link-action.danger{color:#b42318}
    .link-action.danger:hover{background:#fef3f2}
    .card-form-wrap{display:grid;grid-template-columns:280px minmax(0,1fr);gap:24px;align-items:start;margin-top:22px;padding-top:20px;border-top:1px solid #e2e8f0}
    .card-form-wrap:not(.is-open){display:none}
    .card-form-actions{display:flex;justify-content:flex-end;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0}
    .card-form-wrap h3{margin:0 0 12px;font-size:18px}
    .card-type-options{display:flex;gap:10px}
    .type-option{position:relative;flex:1 1 0}
    .type-option input{position:absolute;width:1px;height:1px;min-height:0;opacity:0}
    .type-option span{display:flex;flex-direction:column;justify-content:center;min-height:52px;padding:8px 14px;border:1.5px solid #cbd5e1;border-radius:10px;background:#fff;color:#294b54;font-weight:800;line-height:1.2;cursor:pointer;transition:border-color .15s ease,background .15s ease}
    .type-option small{color:#64748b;font-size:12px;font-weight:700}
    .type-option input:checked+span{border-color:#10a37f;background:#e7f5f0;color:#03715b}
    .type-option input:focus-visible+span{outline:2px solid #10a37f;outline-offset:2px}
    .card-number-field{position:relative}
    .brand-detected{position:absolute;top:50%;right:10px;color:#03715b;font-size:12px;font-weight:900;transform:translateY(-50%);pointer-events:none}
    .client-page .card-number-field .form-control.form-control-sm{padding-right:120px!important;letter-spacing:.06em;font-variant-numeric:tabular-nums}
.summary-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:12px;margin-bottom:18px}
    .summary-card{position:relative;overflow:hidden;background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:18px 20px;box-shadow:0 3px 8px rgba(15,23,42,.06);transition:transform .18s ease,box-shadow .18s ease}
    .summary-card:hover{transform:translateY(-2px);box-shadow:0 10px 22px rgba(9,45,38,.12)}
    .summary-card:before{content:"";position:absolute;top:0;left:0;bottom:0;width:4px;background:linear-gradient(180deg,#087f67,#10a37f)}
    .summary-card strong{display:block;position:relative;z-index:1;font-size:30px;line-height:1.1;color:var(--vet-green)}
    .summary-card span:not(.isdi){display:block;position:relative;z-index:1;margin-top:5px;color:#64748b;font-size:14px}
    .summary-card .summary-marca{position:absolute;right:-12px;bottom:-16px;width:76px;height:76px;margin:0;color:var(--vet-green);opacity:.1;pointer-events:none}
    .section-layout{display:grid;grid-template-columns:minmax(360px,.75fr) minmax(0,1fr);gap:16px;align-items:start}
    .pets-layout{display:grid;grid-template-columns:1fr;gap:16px}
    .pets-form-card{width:100%}
    .pets-list-card{width:100%}
    .wide-section-layout{display:grid;grid-template-columns:1fr;gap:16px}
    .panel-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:22px;box-shadow:0 3px 8px rgba(15,23,42,.07)}
    .panel-card h2{font-size:22px;color:#06152f;margin-bottom:12px}
    .collapsible-form{display:none;scroll-margin-top:90px}
    .collapsible-form.is-open{display:block}
    .empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;gap:6px;padding:26px 16px}
    .empty-state .empty-state-icon{width:38px;height:38px;color:#10a37f;opacity:.55}
    .empty-state strong{color:#33415c;font-size:14px;font-weight:700}
    .empty-state span{color:#8798ad;font-size:13px}
    .list-card{display:grid;gap:12px}
    .item-row{border:1px solid #e2e8f0;border-radius:10px;padding:14px;background:#f8fafc}
    .item-row strong{color:#06152f}
    .item-photo{width:72px;height:72px;object-fit:cover;border-radius:8px;margin:8px 0}
    .table-scroll{overflow:auto}
    .quick-actions{display:flex;gap:10px;flex-wrap:wrap}
    .form-actions{margin-top:12px}
    .compact-form{display:grid;grid-template-columns:repeat(12,1fr);gap:8px 10px}
    .compact-form>div{position:relative;padding-top:8px}
    .compact-form label{margin:2px 0 4px;font-size:13px;line-height:1.2}
    .compact-form input,.compact-form select,.compact-form textarea{min-height:38px;padding:8px;font-size:14px}
    .compact-form textarea{min-height:74px}
    .compact-form .check-row{display:flex;align-items:center;gap:8px;min-height:38px;padding:8px 0 0;font-weight:800;color:#172033}
    .compact-form .check-row input{width:auto;min-height:auto}
    .compact-plan-form{display:grid;grid-template-columns:repeat(12,1fr);gap:8px 10px}
    .compact-plan-form>div{position:relative;padding-top:8px}
    .compact-plan-form label{margin:2px 0 4px;font-size:13px;line-height:1.2}
    .compact-plan-form input,.compact-plan-form select{min-height:38px;padding:8px;font-size:14px}
    .client-page .compact-form .floating-label-activo-sm,
    .client-page .compact-plan-form .floating-label-activo-sm{position:static!important;display:block!important;background:transparent!important;color:#1d4ed8!important;padding:0!important;margin:0 0 4px!important;font-size:14px!important;font-weight:800!important}
    .client-page .compact-form label,
    .client-page .compact-plan-form label{font-size:14px!important;font-weight:800!important}
    .client-page .compact-form>div,
    .client-page .compact-plan-form>div{padding-top:0!important}
    .compact-plan-form .plan-actions{grid-column:span 12;margin-top:4px}
    .span-12{grid-column:span 12}.span-8{grid-column:span 8}.span-6{grid-column:span 6}.span-4{grid-column:span 4}.span-3{grid-column:span 3}
    .span-2{grid-column:span 2}
    .span-compact-check{grid-column:span 4;display:flex;align-items:end;padding-bottom:8px}
    .offers-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
    .offers-grid .item-row{display:flex;align-items:center;gap:13px}
    .item-thumb{display:flex;align-items:center;justify-content:center;flex:0 0 auto;width:82px;height:82px;border-radius:13px;overflow:hidden;background:linear-gradient(135deg,#eef6f3,#d9f3ee)}
    .item-thumb img{width:100%;height:100%;object-fit:cover}
    .item-thumb span{color:#03715b;font-size:13px;font-weight:900;letter-spacing:.04em}
    .item-info{min-width:0}
    .item-info>*::first-letter{text-transform:uppercase}
    .item-info strong{display:block;line-height:1.25}
    .item-info .muted{display:block;margin:3px 0 5px}
    @media(max-width:520px){.item-thumb{width:68px;height:68px}}
    .offer-card{background:#fff;border:1px solid #dbe3ee;border-radius:8px;padding:18px;box-shadow:0 3px 8px rgba(15,23,42,.07);display:flex;flex-direction:column;gap:12px}
    .offer-card>.offer-badge{align-self:flex-start}
    .offer-card>.btn{align-self:flex-end;flex:0 0 auto;margin-top:auto}
    .offer-card h2{font-size:22px;margin:0;color:#06152f}
    .offer-card p{margin:0;color:#64748b;line-height:1.4}
    .offer-list{display:grid;gap:10px;margin:0;padding:0;list-style:none}
    .offer-list li{display:flex;justify-content:space-between;gap:12px;align-items:flex-start;border-top:1px solid #e2e8f0;padding-top:10px}
    .offer-list li{align-items:center}
    .offer-thumb{width:56px;height:56px;align-self:center}
    .offer-thumb span{font-size:11px}
    .offer-info{flex:1 1 auto;min-width:0}
    @media(max-width:520px){.offer-thumb{width:48px;height:48px}}
    .offer-list strong{color:#06152f}
    .offer-list li.has-voucher{position:relative;margin:0 -8px;padding:38px 10px 12px;border:2px solid #ec4899;border-radius:10px;background:linear-gradient(135deg,#fff1f7 0%,#fff 72%);box-shadow:0 5px 14px rgba(236,72,153,.15)}
    .product-voucher-badge{position:absolute;top:8px;left:10px;display:inline-flex;align-items:center;gap:6px;background:#db2777;color:#fff;border-radius:999px;padding:5px 10px;font-size:12px;font-weight:900;letter-spacing:.02em}
    .voucher-code{display:block;margin-top:4px;color:#be185d;font-size:12px;font-weight:800}
    .old-offer-price{color:#94a3b8;font-size:12px;text-decoration:line-through;text-align:right}
    .discounted-offer-price{color:#be185d!important;font-size:19px!important}
    .offer-price{font-weight:700;color:var(--ink);white-space:nowrap}
    .offer-badge{display:none;width:max-content;border-radius:999px;background:#d9f3ee;color:#03715b;font-size:12px;font-weight:900;letter-spacing:.01em;padding:5px 9px}
    .offer-badge::first-letter{text-transform:uppercase}
    .offer-side{display:grid;gap:8px;justify-items:end}
    .offer-add{margin:0}
    .extra-btn.is-cargando{opacity:.55;pointer-events:none}
    .extra-btn.is-listo{animation:carroPop .45s ease}
    @media(prefers-reduced-motion:reduce){.extra-btn.is-listo{animation:none}}
    .extra-btn{display:inline-flex;align-items:center;justify-content:center;width:34px;min-width:34px;height:34px;min-height:34px;padding:0;border-radius:50%;background:linear-gradient(135deg,#087f67,#10a37f);color:#fff;font-size:13px}
    .section-title-row{display:flex;justify-content:space-between;gap:16px;align-items:flex-start;margin-bottom:12px}
    .section-title-row h2{margin-bottom:6px}
    .plan-empty-alert{display:flex;align-items:center;gap:12px;margin-bottom:16px;padding:12px 16px;border:1px solid #fde3b0;border-left:4px solid #f39200;border-radius:10px;background:#fff8ec;color:#7a4a05}
    .plan-empty-alert .isdi{flex:0 0 auto;font-size:22px;color:#f39200}
    .plan-empty-alert strong{display:block;color:#7a4a05;font-size:15px;font-weight:800}
    .plan-empty-alert span{font-size:14px}
    .plan-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
    /* Misma card que el resto del sistema (radio, sombra y fondo del layout) */
    .plan-choice{display:flex;flex-direction:column;gap:14px;padding:22px;border:0;border-radius:var(--radius);background:#fff;box-shadow:var(--shadow)}
    .plan-choice h2{font-size:22px;margin:0;color:#06152f}
    .plan-choice p{margin:0;color:#64748b;line-height:1.45}
    .plan-choice>.btn{margin-top:auto}
    .plan-badge{display:inline-flex;width:max-content;border-radius:999px;background:#e7f5f0;color:#03715b;font-size:12px;font-weight:800;padding:5px 10px}
    .plan-price{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .price-box{background:#f6fafb;border:1px solid #e2eef0;border-radius:10px;padding:12px 14px}
    .price-box span{display:block;color:#64748b;font-size:12px;font-weight:900;text-transform:uppercase}
    .price-box strong{display:block;color:#06152f;font-size:22px;margin-top:4px}
    .plan-includes{display:flex;gap:8px;flex-wrap:wrap;margin:0;padding:0;list-style:none}
    .plan-includes li{background:#ecfdf5;color:#14532d;border-radius:999px;padding:7px 10px;font-weight:800;font-size:13px}
    .notice-list{display:grid;gap:10px;margin-bottom:18px}
    .notice-item{background:#eff6ff;border:1px solid #bfdbfe;border-radius:8px;padding:12px;display:flex;justify-content:space-between;gap:12px;align-items:center}
    .notice-item strong{color:#1e3a8a}
    .payment-register{display:none;margin-top:8px}.payment-register.is-visible{display:inline-flex}
    .tracking-layout{display:grid;grid-template-columns:minmax(0,2fr) minmax(280px,.8fr);gap:16px;align-items:start}
    .tracking-map{min-height:330px;border:1px solid #dbe3ee;border-radius:8px;background:#f8fafc;overflow:hidden;display:flex;align-items:center;justify-content:center;color:#64748b;font-weight:900}
    .tracking-map-empty{display:flex;flex-direction:column;align-items:center;gap:8px;text-align:center;padding:0 24px}
    .tracking-map-empty-icon{width:34px;height:34px;color:#94a3b8;opacity:.7}
    .tracking-map iframe{width:100%;height:360px;border:0}
    .tracking-timeline{display:grid;gap:10px;margin-top:14px}
    .tracking-steps{display:grid;grid-template-columns:repeat(4,1fr);gap:0;margin:0 0 20px}
    .tracking-step{position:relative;display:flex;flex-direction:column;align-items:center;gap:7px;padding:0 4px;text-align:center;font-size:12px;font-weight:800;color:#94a3b8}
    .tracking-step:not(:first-child):before{content:"";position:absolute;top:16px;left:-50%;width:100%;height:2px;background:#e2e8f0;z-index:0}
    .tracking-step.done:not(:first-child):before,.tracking-step.current:not(:first-child):before{background:#10a37f}
    .tracking-step-num{display:flex;align-items:center;justify-content:center;position:relative;z-index:1;width:32px;height:32px;border-radius:50%;background:#e5e7eb;color:#94a3b8;font-size:13px;font-weight:900;transition:background .2s ease,color .2s ease}
    .tracking-step.done .tracking-step-num,.tracking-step.current .tracking-step-num{background:linear-gradient(135deg,#087f67,#10a37f);color:#fff;box-shadow:0 3px 8px rgba(8,127,103,.3)}
    .tracking-step.done,.tracking-step.current{color:#087f67}
    .tracking-event{border-left:4px solid #2563eb;background:#f8fafc;border-radius:8px;padding:10px 12px}
    .driver-card{display:grid;gap:10px}.driver-photo{width:100%;max-height:170px;object-fit:cover;border-radius:8px;border:1px solid #dbe3ee;background:#f8fafc}
    .vehicle-line{display:grid;grid-template-columns:110px 1fr;gap:8px;border-bottom:1px solid #e2e8f0;padding-bottom:7px}
    @media(max-width:950px){.order-body,.card-form-wrap{grid-template-columns:minmax(0,1fr)}.order-side{padding:14px 0 0;border-left:0;border-top:1px solid #eef2f6}.order-detail-grid{grid-template-columns:1fr}.order-head .order-status{margin-left:0}.card-preview{max-width:320px}.client-hero,.section-layout,.summary-grid,.offers-grid,.plan-grid,.tracking-layout{grid-template-columns:1fr}.quick-actions a{width:100%}.plan-price{grid-template-columns:1fr}.section-title-row{display:grid}.span-12,.span-8,.span-6,.span-4,.span-3,.span-2,.span-compact-check,.compact-plan-form .plan-actions{grid-column:span 12}.tracking-steps{grid-template-columns:1fr 1fr}}
    @media(max-width:600px){.section-head,.form-toggle-row{flex-direction:column;align-items:stretch}.section-head h2,.form-toggle-row h2{font-size:21px}.btn-edit,.btn-form-toggle{width:100%}.item-row.between{flex-direction:column;align-items:stretch}.item-row .actions{display:flex!important;justify-content:flex-end;width:auto;padding-top:10px;border-top:1px solid #e2e8f0}.item-row .actions>*{width:auto!important}.edit-actions,.card-form-actions{flex-direction:column-reverse}.card-type-options{flex-direction:column}.order-head{gap:8px 18px;padding:12px 14px}.order-body{padding:14px}.order-detail summary{padding:12px 14px}.order-detail-grid{padding:0 14px 14px}}
</style>

<div class="client-page">
    @php
        $pedidoDespacho = $user->pedidos->whereNotIn('estado', ['entregado', 'cancelado'])->sortByDesc('created_at')->first();
        $estadosDespacho = ['listo_despacho', 'reparto_asignado', 'en_camino', 'asignado', 'en_ruta'];
        $fotosReferencia = [
            'Mordedor dental' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/2/2d/Blue_dog_bone_toy.JPG/500px-Blue_dog_bone_toy.JPG',
            'Pelota resistente' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/5/55/Tennisball.jpg/500px-Tennisball.jpg',
            'Antiparasitario mensual' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/5/5c/Deworming_tablet_packaging.jpg/500px-Deworming_tablet_packaging.jpg',
            'Suplemento articular' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/b/b0/Omega_3_capsules_in_white_bottle_%2852715127894%29.jpg/500px-Omega_3_capsules_in_white_bottle_%2852715127894%29.jpg',
            'Correa reflectante' => 'https://upload.wikimedia.org/wikipedia/commons/3/3e/PPD-Leash-PinkGreenStripes.jpg',
            'Dispensador de alimento' => 'https://upload.wikimedia.org/wikipedia/commons/b/bc/Pet_Food_Dispenser.png',
            'Shampoo piel sensible' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/9/96/Green_shampoo_bottle.jpg/500px-Green_shampoo_bottle.jpg',
            'Toallitas higienicas' => 'https://thumb.wikimedia.org/wikipedia/commons/thumb/7/77/Wet_wipes_on_a_shelf.jpg/500px-Wet_wipes_on_a_shelf.jpg',
        ];
        $despachoEnCurso = $pedidoDespacho && in_array($pedidoDespacho->estado, $estadosDespacho, true);
        $menuCliente = [
            ['titulo' => 'Mi cuenta', 'items' => [
                ['seccion' => 'resumen', 'texto' => 'Resumen', 'icono' => 'inicio'],
                ['seccion' => 'perfil', 'texto' => 'Mi perfil', 'icono' => 'usuario'],
                ['seccion' => 'contrasena', 'texto' => 'Mi contraseña', 'icono' => 'candado'],
                ['seccion' => 'compras', 'texto' => 'Mis compras', 'icono' => 'compras'],
                ['seccion' => 'tarjetas', 'texto' => 'Tarjetas', 'icono' => 'tarjeta'],
            ]],
            ['titulo' => 'Mis servicios', 'items' => [
                ['seccion' => 'mi-plan', 'texto' => 'Mi plan', 'icono' => 'suscripcion'],
                ['seccion' => 'mascotas', 'texto' => 'Mascotas', 'icono' => 'mascota'],
                ['seccion' => 'direcciones', 'texto' => 'Direcciones', 'icono' => 'locacion'],
                ['seccion' => 'pedido', 'texto' => 'Pedidos programados', 'icono' => 'carrito'],
                ['seccion' => 'ofertas', 'texto' => 'Ofertas', 'icono' => 'oferta'],
                ['seccion' => 'tracking', 'texto' => $despachoEnCurso ? 'Pedido en despacho' : 'Ver tracking', 'icono' => 'seguimiento', 'destacado' => $despachoEnCurso],
            ]],
            ['titulo' => 'Más', 'items' => [
                ['url' => route('encuesta.usuario'), 'texto' => 'Encuesta', 'icono' => 'encuesta'],
                ['url' => route('vouchers.usuario'), 'texto' => 'Mis vouchers', 'icono' => 'cupon'],
            ]],
        ];
    @endphp
<div class="menu-lateral-layout" data-menu-memoria="cliente">
<x-menu-lateral etiqueta="Navegación cuenta cliente" :grupos="$menuCliente" />

<div class="menu-lateral-contenido">

@if($notificaciones->isNotEmpty())
    <div class="notice-list">
        @foreach($notificaciones as $notificacion)
            <div class="notice-item">
                <span>
                    <strong>{{ $notificacion->titulo }}</strong><br>
                    <span class="muted">{{ $notificacion->mensaje }}</span>
                </span>
                @if($notificacion->url)
                    <a class="btn btn-success" href="{{ $notificacion->url }}">Agregar extras</a>
                @endif
            </div>
        @endforeach
    </div>
@endif

<section class="menu-lateral-seccion is-activa" id="cliente-resumen" data-menu-panel="resumen">
    <div class="client-hero">
        <h1>Clientes y mascotas</h1>
        <p class="muted">Administra mascotas, direcciones, pedidos recurrentes y productos adicionales desde secciones separadas.</p>
    </div>

    <div class="summary-grid">
        <div class="summary-card"><strong>{{ $user->mascotas->count() }}</strong><span>Mascotas inscritas</span><x-icono nombre="mascota" class="summary-marca" /></div>
        <div class="summary-card"><strong>{{ $user->direcciones->count() }}</strong><span>Direcciones guardadas</span><x-icono nombre="locacion" class="summary-marca" /></div>
        <div class="summary-card"><strong>{{ $user->planesPedido->count() }}</strong><span>Pedidos recurrentes</span><x-icono nombre="suscripcion" class="summary-marca" /></div>
        <div class="summary-card"><strong>{{ $vouchersPlan->count() }}</strong><span>Vouchers disponibles</span><x-icono nombre="cupon" class="summary-marca" /></div>
    </div>
    <div class="panel-card">
        <h2>Mis pedidos frecuentes</h2>
        <div class="table-scroll">
            <table>
                <thead><tr><th>Producto</th><th>Mascota</th><th>Voucher</th><th>Frecuencia</th><th>Próxima entrega</th><th>Direccion</th></tr></thead>
                <tbody>
                    @forelse($user->planesPedido as $plan)
                        <tr>
                            <td>{{ $plan->producto->nombre }}</td>
                            <td>{{ $plan->mascota?->nombre ?? 'General' }}</td>
                            <td>{{ $plan->voucher?->codigo ?? 'Sin voucher' }}<br><span class="muted">{{ $plan->voucher?->titulo }}</span></td>
                            <td>{{ $plan->frecuencia }} x {{ $plan->cantidad }}</td>
                            <td>{{ $plan->proxima_entrega->format('d-m-Y') }}</td>
                            <td>{{ $plan->direccion_entrega }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="muted">Aun no tienes pedidos recurrentes.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="panel-card" style="margin-top:16px">
        <div class="section-title-row">
            <div>
                <h2>Productos adicionales</h2>
                <p class="muted">Medicamentos, juguetes, utensilios y otros productos se agregan al carro junto con el pedido base.</p>
            </div>
            <a class="btn btn-success" href="{{ route('tienda.catalogo', ['categoria' => 'adicional']) }}">Agregar adicionales</a>
        </div>
        <div class="quick-actions">
            <a class="btn" href="{{ route('tienda.catalogo', ['categoria' => 'medicamento']) }}">Medicamentos</a>
            <a class="btn" href="{{ route('tienda.catalogo', ['categoria' => 'juguete']) }}">Accesorios y Juguetes</a>
            <a class="btn" href="{{ route('tienda.catalogo', ['categoria' => 'utensilio']) }}">Utensilios</a>
        </div>
        <hr>
        <div class="offers-grid">
            @foreach($productos->whereIn('categoria', ['medicamento','juguete','utensilio'])->take(6) as $producto)
                <div class="item-row">
                    @php
                        $fotoProducto = $producto->foto_url
                            ? asset($producto->foto_url)
                            : ($fotosReferencia[$producto->nombre] ?? null);
                    @endphp
                    <div class="item-thumb">
                        @if($fotoProducto)
                            <img src="{{ $fotoProducto }}" alt="{{ $producto->nombre }}" loading="lazy">
                        @else
                            <span>{{ strtoupper(substr($producto->categoria, 0, 3)) }}</span>
                        @endif
                    </div>
                    <div class="item-info">
                        <strong>{{ $producto->nombre }}</strong>
                        <span class="muted">{{ $producto->categoria }} {{ $producto->marca }}</span>
                        <span class="offer-price">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

@php
    $perfilCliente = $user->perfilCliente;
    $celularPerfil = substr(preg_replace('/\D/', '', (string) $user->telefono), -9);
    $erroresPerfil = $errors->getBag('perfil');
    $erroresPassword = $errors->getBag('password');
    $erroresTarjeta = $errors->getBag('tarjeta');
    $ojoPassword = '<svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>'
        . '<svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A10 10 0 0 1 12 5c6.5 0 10 7 10 7a17.6 17.6 0 0 1-3.2 4.2M6.6 6.6C3.8 8.4 2 12 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';
@endphp

<section class="menu-lateral-seccion" id="cliente-perfil" data-menu-panel="perfil">
    <div class="section-head">
        <div>
            <h2>Mi perfil</h2>
            <p class="muted">Tus datos personales. Se usan para tus compras, despachos y boletas.</p>
        </div>
        <button type="button" class="btn-edit" data-edit-toggle="form-perfil" aria-controls="form-perfil"><x-icono nombre="editar" />Editar datos</button>
    </div>
    <div class="panel-card">
        <form method="POST" action="{{ route('cliente.perfil.update') }}" id="form-perfil" class="editable-form {{ $erroresPerfil->any() ? 'is-editing' : '' }}" data-keep-open="1">
            @csrf
            @method('PATCH')
            <fieldset @disabled(!$erroresPerfil->any())>
                <div class="compact-form">
                    <div class="span-6 {{ $erroresPerfil->has('nombres') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_nombres">Nombre</label>
                        <input class="form-control form-control-sm" id="perfil_nombres" name="nombres" value="{{ old('nombres', $user->nombres ?? $user->name) }}" autocomplete="given-name" maxlength="120" required>
                        @error('nombres', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('apellidos') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_apellidos">Apellido</label>
                        <input class="form-control form-control-sm" id="perfil_apellidos" name="apellidos" value="{{ old('apellidos', $user->apellidos) }}" autocomplete="family-name" maxlength="120" required>
                        @error('apellidos', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('rut') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_rut">RUT</label>
                        <input class="form-control form-control-sm" id="perfil_rut" name="rut" value="{{ old('rut') ? \App\Rules\RutChileno::formatear(old('rut')) : \App\Rules\RutChileno::formatear($perfilCliente?->rut) }}" placeholder="12.345.678-9" maxlength="12" data-rut required>
                        @error('rut', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('fecha_nacimiento') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_fecha_nacimiento">Fecha de nacimiento</label>
                        <input class="form-control form-control-sm" type="date" id="perfil_fecha_nacimiento" name="fecha_nacimiento" value="{{ old('fecha_nacimiento', optional($user->fecha_nacimiento)->toDateString()) }}" min="1900-01-01" max="{{ now()->subDay()->toDateString() }}" autocomplete="bday" required>
                        @error('fecha_nacimiento', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('celular') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_celular">Celular</label>
                        <div class="phone-group">
                            <span class="phone-prefix" title="Chile">
                                <svg viewBox="0 0 30 20" aria-hidden="true"><rect width="30" height="20" fill="#fff"/><rect y="10" width="30" height="10" fill="#d52b1e"/><rect width="10" height="10" fill="#0039a6"/><path fill="#fff" d="M5 2.4l.59 1.79h1.88L5.95 5.31l.58 1.79L5 6l-1.53 1.1.58-1.79-1.52-1.12h1.88z"/></svg>
                                +56
                            </span>
                            <input class="form-control form-control-sm" type="tel" id="perfil_celular" name="celular" value="{{ old('celular', strlen($celularPerfil) === 9 ? $celularPerfil : '') }}" inputmode="numeric" pattern="[0-9]{9}" maxlength="9" placeholder="912345678" autocomplete="tel-national" aria-describedby="perfil_celular_ayuda" data-solo-digitos required>
                        </div>
                        <small class="field-hint" id="perfil_celular_ayuda">Ingrese 9 dígitos</small>
                        @error('celular', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                    <div class="span-6 {{ $erroresPerfil->has('email') ? 'has-error' : '' }}">
                        <label class="floating-label-activo-sm" for="perfil_email">Email</label>
                        <input class="form-control form-control-sm" type="email" id="perfil_email" name="email" value="{{ old('email', $user->email) }}" autocomplete="email" maxlength="255" required>
                        @error('email', 'perfil')<small class="field-error">{{ $message }}</small>@enderror
                    </div>
                </div>
            </fieldset>
            <div class="edit-actions">
                <button type="button" class="btn btn-secondary" data-edit-cancel>Cancelar</button>
                <button type="submit" class="btn btn-success">Guardar cambios</button>
            </div>
        </form>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-contrasena" data-menu-panel="contrasena">
    <div class="section-head">
        <div>
            <h2>Mi contraseña</h2>
            <p class="muted">Usa una contraseña segura que no ocupes en otros sitios.</p>
        </div>
        <button type="button" class="btn-edit" data-edit-toggle="form-password" aria-controls="form-password"><x-icono nombre="editar" />Cambiar contraseña</button>
    </div>
    <div class="panel-card">
        <form method="POST" action="{{ route('cliente.password.update') }}" id="form-password" class="editable-form password-form {{ $erroresPassword->any() ? 'is-editing' : '' }}" data-keep-open="1">
            @csrf
            @method('PATCH')
            <div class="compact-form solo-vista">
                <div class="span-12">
                    <label class="floating-label-activo-sm" for="password_vista">Contraseña</label>
                    <input class="form-control form-control-sm" type="password" id="password_vista" value="contrasena" disabled aria-describedby="password_vista_ayuda">
                    <small class="field-hint" id="password_vista_ayuda">
                        @if($user->password_cambiada_at)
                            Última actualización: {{ $user->password_cambiada_at->locale('es')->translatedFormat('j \d\e F \d\e Y') }}
                        @else
                            Por seguridad no mostramos tu contraseña actual.
                        @endif
                    </small>
                </div>
            </div>
            <fieldset @disabled(!$erroresPassword->any()) class="solo-edicion">
                @unless($requierePasswordActual)
                    <p class="info-note"><x-icono nombre="candado" />Ingresaste con tu cuenta VET SDI, así que puedes crear una contraseña para esta tienda sin ingresar la actual.</p>
                @endunless
                <div class="compact-form">
                    @php
                        $camposPassword = array_filter([
                            $requierePasswordActual ? ['current_password', 'Contraseña actual', 'current-password'] : null,
                            ['password', 'Nueva contraseña', 'new-password'],
                            ['password_confirmation', 'Repetir nueva contraseña', 'new-password'],
                        ]);
                    @endphp
                    @foreach($camposPassword as [$campo, $etiqueta, $autocompletar])
                        <div class="span-12 {{ $erroresPassword->has($campo) ? 'has-error' : '' }}">
                            <label class="floating-label-activo-sm" for="pass_{{ $campo }}">{{ $etiqueta }}</label>
                            <div class="pass-field">
                                <input class="form-control form-control-sm" type="password" id="pass_{{ $campo }}" name="{{ $campo }}" autocomplete="{{ $autocompletar }}" maxlength="72" required>
                                <button type="button" class="pass-eye" data-pass-toggle="pass_{{ $campo }}" aria-label="Mostrar contraseña" aria-pressed="false">{!! $ojoPassword !!}</button>
                            </div>
                            @error($campo, 'password')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                    @endforeach
                    <div class="span-12">
                        <ul class="pass-rules" aria-live="polite">
                            <li data-regla="largo">Al menos 8 caracteres</li>
                            <li data-regla="mezcla">Letras y números</li>
                            <li data-regla="coincide">Ambas contraseñas coinciden</li>
                        </ul>
                    </div>
                </div>
            </fieldset>
            <div class="edit-actions">
                <button type="button" class="btn btn-secondary" data-edit-cancel>Cancelar</button>
                <button type="submit" class="btn btn-success">Guardar contraseña</button>
            </div>
        </form>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-compras" data-menu-panel="compras">
    @php
        $compras = $user->pedidos->sortByDesc('created_at')->values();
        $estadosCompra = [
            'recibido' => ['Pedido recibido', 'curso'],
            'en_preparacion' => ['En preparación', 'curso'],
            'preparando' => ['En preparación', 'curso'],
            'listo_despacho' => ['Listo para despacho', 'curso'],
            'reparto_asignado' => ['Repartidor asignado', 'curso'],
            'asignado' => ['Repartidor asignado', 'curso'],
            'en_camino' => ['En camino', 'curso'],
            'en_ruta' => ['En camino', 'curso'],
            'entregado' => ['Entregado', 'entregado'],
            'cancelado' => ['Cancelado', 'cancelado'],
        ];
        $metodosPago = [
            'simulado_local' => 'Pago en línea',
            'transferencia' => 'Transferencia',
            'efectivo_entrega' => 'Efectivo contra entrega',
            'tarjeta_guardada' => 'Tarjeta guardada',
        ];
        $grupoCompra = fn ($pedido) => $estadosCompra[$pedido->estado][1] ?? 'curso';
        $conteoCompras = $compras->countBy($grupoCompra);
    @endphp
    <div class="section-head">
        <div>
            <h2>Mis compras</h2>
            <p class="muted">Revisa el estado y el detalle de todas las compras que has realizado.</p>
        </div>
    </div>

    @if($compras->isEmpty())
        <div class="panel-card">
            <div class="empty-state">
                <x-icono nombre="compras" class="empty-state-icon" />
                <strong>Aún no tienes compras</strong>
                <span>Cuando compres en la tienda, verás aquí tu historial.</span>
                <a class="btn btn-orange" style="margin-top:10px" href="{{ route('tienda.catalogo') }}">Ir a la tienda</a>
            </div>
        </div>
    @else
        <div class="orders-toolbar" role="group" aria-label="Filtrar compras">
            <button type="button" class="order-filter active" data-order-filter="todas" aria-pressed="true">Todas ({{ $compras->count() }})</button>
            <button type="button" class="order-filter" data-order-filter="curso" aria-pressed="false">En curso ({{ $conteoCompras['curso'] ?? 0 }})</button>
            <button type="button" class="order-filter" data-order-filter="entregado" aria-pressed="false">Entregadas ({{ $conteoCompras['entregado'] ?? 0 }})</button>
            <button type="button" class="order-filter" data-order-filter="cancelado" aria-pressed="false">Canceladas ({{ $conteoCompras['cancelado'] ?? 0 }})</button>
        </div>
        <div class="order-list">
            @foreach($compras as $compra)
                @php
                    [$estadoTexto, $estadoGrupo] = $estadosCompra[$compra->estado] ?? [ucfirst(str_replace('_', ' ', $compra->estado)), 'curso'];
                    $tarjetaPago = data_get($compra->pago?->detalle, 'tarjeta.descripcion');
                    $medioPago = $tarjetaPago ?: ($metodosPago[$compra->pago?->metodo] ?? ($compra->pago ? ucfirst(str_replace('_', ' ', $compra->pago->metodo)) : 'Sin información'));
                    $esRetiro = $compra->direccion_entrega === 'Retiro en tienda';
                    $unidades = $compra->items->sum('cantidad');
                @endphp
                <article class="order-card" data-order-group="{{ $estadoGrupo }}">
                    <header class="order-head">
                        <div class="order-meta"><span>Fecha de compra</span><strong>{{ $compra->created_at->locale('es')->translatedFormat('j \d\e F \d\e Y') }}</strong></div>
                        <div class="order-meta"><span>Total</span><strong>${{ number_format($compra->total, 0, ',', '.') }}</strong></div>
                        <div class="order-meta"><span>N° de pedido</span><strong>{{ $compra->codigo_tracking }}</strong></div>
                        <span class="order-status status-{{ $estadoGrupo }}">{{ $estadoTexto }}</span>
                    </header>
                    <div class="order-body">
                        <div>
                            <p class="order-note">
                                @if($estadoGrupo === 'entregado')
                                    Entregado{{ $compra->entregado_at ? ' el ' . $compra->entregado_at->locale('es')->translatedFormat('j \d\e F') : '' }}
                                @elseif($estadoGrupo === 'cancelado')
                                    Compra cancelada
                                @else
                                    {{ $esRetiro ? 'Retiro en tienda' : 'Llega' }}{{ $compra->fecha_entrega ? ' el ' . $compra->fecha_entrega->locale('es')->translatedFormat('l j \d\e F') : '' }}
                                @endif
                                <span>· {{ $unidades }} {{ $unidades === 1 ? 'producto' : 'productos' }}</span>
                                @if($compra->frecuencia && $compra->frecuencia !== 'unico')<span class="order-tag">Pedido programado</span>@endif
                            </p>
                            <ul class="order-items">
                                @foreach($compra->items as $item)
                                    @php
                                        $fotoItem = $item->producto?->foto_url
                                            ? asset($item->producto->foto_url)
                                            : ($fotosReferencia[$item->producto_nombre] ?? null);
                                    @endphp
                                    <li class="order-item">
                                        <span class="item-thumb">
                                            @if($fotoItem)
                                                <img src="{{ $fotoItem }}" alt="" loading="lazy">
                                            @else
                                                <span>{{ strtoupper(mb_substr($item->producto_nombre, 0, 3)) }}</span>
                                            @endif
                                        </span>
                                        <span class="order-item-info">
                                            <strong>{{ $item->producto_nombre }}</strong>
                                            <span>{{ $item->producto_marca ? $item->producto_marca . ' · ' : '' }}{{ $item->cantidad }} {{ $item->cantidad === 1 ? 'unidad' : 'unidades' }}</span>
                                        </span>
                                        <span class="order-item-price">${{ number_format($item->total, 0, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="order-side">
                            <a class="btn btn-success" href="{{ route('tracking.show', $compra->codigo_tracking) }}">{{ $estadoGrupo === 'curso' ? 'Seguir pedido' : 'Ver detalle de envío' }}</a>
                            @if($compra->items->whereNotNull('producto_id')->isNotEmpty())
                                <form method="POST" action="{{ route('cliente.compras.repetir', $compra) }}" data-cargando-tienda>
                                    @csrf
                                    <button type="submit" class="btn btn-orange-outline">Volver a comprar</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    <details class="order-detail">
                        <summary>Ver detalle de la compra</summary>
                        <div class="order-detail-grid">
                            <div>
                                <h3>{{ $esRetiro ? 'Retiro' : 'Despacho' }}</h3>
                                <p>
                                    {{ $esRetiro ? 'Retiro en tienda' : $compra->direccion_entrega }}
                                    @if(!$esRetiro && ($compra->ciudad_nombre || $compra->region_nombre))
                                        <br><span class="muted">{{ collect([$compra->ciudad_nombre, $compra->region_nombre])->filter()->implode(', ') }}</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <h3>Medio de pago</h3>
                                <p>{{ $medioPago }}@if($compra->pagado_at)<br><span class="muted">Pagado el {{ $compra->pagado_at->format('d-m-Y H:i') }}</span>@endif</p>
                            </div>
                            <div class="order-totals">
                                <h3>Resumen</h3>
                                <div><span>Subtotal</span><span>${{ number_format($compra->subtotal, 0, ',', '.') }}</span></div>
                                <div><span>Envío</span><span>{{ $compra->costo_envio > 0 ? '$' . number_format($compra->costo_envio, 0, ',', '.') : 'Gratis' }}</span></div>
                                @if($compra->descuento_total > 0)
                                    <div><span>Descuento</span><span>-${{ number_format($compra->descuento_total, 0, ',', '.') }}</span></div>
                                @endif
                                <div class="order-total"><span>Total</span><span>${{ number_format($compra->total, 0, ',', '.') }}</span></div>
                            </div>
                        </div>
                    </details>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="menu-lateral-seccion" id="cliente-tarjetas" data-menu-panel="tarjetas">
    @php
        $tarjetas = $user->tarjetas;
        $maxTarjetas = \App\Models\TarjetaCliente::MAXIMO_POR_CLIENTE;
        $puedeAgregarTarjeta = $tarjetas->count() < $maxTarjetas;
    @endphp
    <div class="section-head">
        <div>
            <h2>Mis tarjetas</h2>
            <p class="muted">{{ $tarjetas->count() }} de {{ $maxTarjetas }} tarjetas guardadas. Al comprar, usaremos automáticamente tu tarjeta predeterminada.</p>
        </div>
        @if($puedeAgregarTarjeta)
            <button type="button" class="btn-form-toggle" data-form-toggle="form-tarjeta" data-label-abierto="Cerrar formulario" data-label-cerrado="Agregar tarjeta" aria-controls="form-tarjeta" aria-expanded="{{ $erroresTarjeta->any() ? 'true' : 'false' }}"><x-icono nombre="plus" /><span data-toggle-label>{{ $erroresTarjeta->any() ? 'Cerrar formulario' : 'Agregar tarjeta' }}</span></button>
        @endif
    </div>
    <div class="panel-card">

        <div class="saved-cards">
            @foreach($tarjetas as $tarjeta)
                <div class="saved-card">
                    <div class="card-visual marca-{{ \Illuminate\Support\Str::slug($tarjeta->marca) }} {{ $tarjeta->vencida ? 'is-vencida' : '' }}">
                        <div class="card-top">
                            <div><span class="card-brand">{{ $tarjeta->marca }}</span><span class="card-kind">{{ $tarjeta->tipo === 'debito' ? 'Débito' : 'Crédito' }}</span></div>
                            <span class="card-chip" aria-hidden="true"></span>
                        </div>
                        <div class="card-number" aria-label="Terminada en {{ $tarjeta->ultimos_digitos }}">•••• •••• •••• {{ $tarjeta->ultimos_digitos }}</div>
                        <div class="card-bottom">
                            <div><span>Titular</span><strong>{{ $tarjeta->titular }}</strong></div>
                            <div><span>Vence</span><strong>{{ $tarjeta->vencimiento }}</strong></div>
                        </div>
                    </div>
                    <div class="card-meta">
                        @if($tarjeta->predeterminada)<span class="card-badge badge-default">Predeterminada</span>@endif
                        @if($tarjeta->vencida)<span class="card-badge badge-vencida">Vencida</span>@endif
                        @if($tarjeta->alias)<span class="muted">{{ $tarjeta->alias }}</span>@endif
                    </div>
                    <div class="card-actions">
                        @unless($tarjeta->predeterminada || $tarjeta->vencida)
                            <form method="POST" action="{{ route('cliente.tarjetas.predeterminada', $tarjeta) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="link-action">Usar como predeterminada</button>
                            </form>
                        @endunless
                        <form method="POST" action="{{ route('cliente.tarjetas.destroy', $tarjeta) }}" data-confirmar="¿Eliminar la tarjeta terminada en {{ $tarjeta->ultimos_digitos }}?">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="link-action danger"><x-icono nombre="eliminar" /> Eliminar</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        @if($tarjetas->isEmpty())
            <div class="empty-state">
                <x-icono nombre="tarjeta" class="empty-state-icon" />
                <strong>Aún no tienes tarjetas guardadas</strong>
                <span>Agrega una tarjeta para pagar tus compras más rápido.</span>
            </div>
        @endif

        @if($puedeAgregarTarjeta)
            <div class="card-form-wrap {{ $erroresTarjeta->any() ? 'is-open' : '' }}" id="form-tarjeta">
                <div class="card-preview">
                    <div class="card-visual" id="tarjeta_preview">
                        <div class="card-top">
                            <div><span class="card-brand" data-preview="marca">Tarjeta</span><span class="card-kind" data-preview="tipo">{{ old('tipo') === 'debito' ? 'Débito' : 'Crédito' }}</span></div>
                            <span class="card-chip" aria-hidden="true"></span>
                        </div>
                        <div class="card-number" data-preview="numero">•••• •••• •••• ••••</div>
                        <div class="card-bottom">
                            <div><span>Titular</span><strong data-preview="titular">{{ old('titular') ?: 'NOMBRE APELLIDO' }}</strong></div>
                            <div><span>Vence</span><strong data-preview="vencimiento">{{ old('vencimiento') ?: 'MM/AA' }}</strong></div>
                        </div>
                    </div>
                </div>
                <form method="POST" action="{{ route('cliente.tarjetas.store') }}" data-keep-open="1" autocomplete="on">
                    @csrf
                    <h3 class="form-title">Datos de la tarjeta de débito o crédito</h3>
                    <p class="info-note"><x-icono nombre="candado" />Por seguridad solo guardamos la marca, los últimos 4 dígitos y el vencimiento. Nunca almacenamos el número completo ni el código de seguridad (CVV).</p>
                    <div class="compact-form">
                        <div class="span-12">
                            <span class="floating-label-activo-sm">Tipo de tarjeta</span>
                            <div class="card-type-options" role="radiogroup" aria-label="Tipo de tarjeta">
                                <label class="type-option"><input type="radio" name="tipo" value="debito" @checked(old('tipo') === 'debito') required><span>Débito<small>Redcompra</small></span></label>
                                <label class="type-option"><input type="radio" name="tipo" value="credito" @checked(old('tipo', 'credito') === 'credito')><span>Crédito<small>Visa, Mastercard, Amex, Diners</small></span></label>
                            </div>
                            @error('tipo', 'tarjeta')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="span-6 {{ $erroresTarjeta->has('numero_tarjeta') ? 'has-error' : '' }}">
                            <label class="floating-label-activo-sm" for="tarjeta_numero">Número de tarjeta</label>
                            <div class="card-number-field">
                                <input class="form-control form-control-sm" id="tarjeta_numero" name="numero_tarjeta" inputmode="numeric" autocomplete="cc-number" maxlength="23" placeholder="0000 0000 0000 0000" required>
                                <span class="brand-detected" id="tarjeta_marca" aria-live="polite"></span>
                            </div>
                            @error('numero_tarjeta', 'tarjeta')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="span-6 {{ $erroresTarjeta->has('titular') ? 'has-error' : '' }}">
                            <label class="floating-label-activo-sm" for="tarjeta_titular">Nombre del titular</label>
                            <input class="form-control form-control-sm" id="tarjeta_titular" name="titular" value="{{ old('titular') }}" autocomplete="cc-name" maxlength="120" placeholder="Como aparece en la tarjeta" required>
                            @error('titular', 'tarjeta')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="span-3 {{ $erroresTarjeta->has('vencimiento') ? 'has-error' : '' }}">
                            <label class="floating-label-activo-sm" for="tarjeta_vencimiento">Vencimiento</label>
                            <input class="form-control form-control-sm" id="tarjeta_vencimiento" name="vencimiento" value="{{ old('vencimiento') }}" inputmode="numeric" autocomplete="cc-exp" maxlength="5" placeholder="MM/AA" required>
                            @error('vencimiento', 'tarjeta')<small class="field-error">{{ $message }}</small>@enderror
                        </div>
                        <div class="span-5">
                            <label class="floating-label-activo-sm" for="tarjeta_alias">Alias (opcional)</label>
                            <input class="form-control form-control-sm" id="tarjeta_alias" name="alias" value="{{ old('alias') }}" maxlength="60" placeholder="Ej: Tarjeta personal">
                        </div>
                        <div class="span-4">
                            <label class="check-row"><input type="checkbox" name="predeterminada" value="1" @checked(old('predeterminada') || $tarjetas->isEmpty())> Usar como predeterminada</label>
                        </div>
                    </div>
                    <div class="card-form-actions">
                        <button type="button" class="btn btn-secondary" data-form-toggle-cerrar="form-tarjeta">Cancelar</button>
                        <button type="submit" class="btn btn-success">Guardar tarjeta</button>
                    </div>
                </form>
            </div>
        @else
            <p class="info-note" style="margin-top:18px"><x-icono nombre="tarjeta" />Alcanzaste el máximo de {{ $maxTarjetas }} tarjetas. Elimina una para agregar otra.</p>
        @endif
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-mi-plan" data-menu-panel="mi-plan">
    @php
        $planActivoComercial = collect($planesDisponibles)->firstWhere('slug', $user->plan_preferido);
        $planesMejora = collect($planesDisponibles)->reject(fn($plan) => $plan['slug'] === ($planActivoComercial['slug'] ?? null));
    @endphp
    <div class="section-head">
        <div>
            <h2>Suscripciones</h2>
            <p class="muted">
                @if($planActivoComercial)
                    Revisa tu plan actual y mejóralo cuando quieras.
                @else
                    Elige una alternativa y luego crea tu primer pedido recurrente. Los planes permiten alimento automático, vouchers, QR, historial y servicios programados.
                @endif
            </p>
        </div>
    </div>
    @if($planActivoComercial)
        <div class="section-layout">
            <div class="panel-card">
                <span class="plan-badge">{{ $planActivoComercial['etiqueta'] }}</span>
                <h2>Mi plan actual</h2>
                <h3>{{ $planActivoComercial['nombre'] }}</h3>
                <p class="muted">{{ $planActivoComercial['descripcion'] }}</p>
                <div class="plan-price">
                    <div class="price-box">
                        <span>Pagado al contratar</span>
                        <strong>${{ number_format($planActivoComercial['valor_inicial'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="price-box">
                        <span>Cargo mensual</span>
                        <strong>${{ number_format($planActivoComercial['valor_mensual'], 0, ',', '.') }}</strong>
                    </div>
                </div>
                <ul class="plan-includes">
                    @foreach($planActivoComercial['incluye'] as $item)
                        <li>{{ $item }}</li>
                    @endforeach
                </ul>
                <div class="quick-actions" style="margin-top:14px">
                    <button class="btn" type="button" data-menu-ir="pedido">Configurar pedido recurrente</button>
                    <button class="btn btn-success" type="button" data-menu-ir="ofertas">Ver beneficios</button>
                </div>
            </div>
            <div class="panel-card">
                <h2>Mejorar plan</h2>
                <p class="muted">Puedes cambiar a un plan superior o complementar con otro beneficio. El boton te lleva a la pasarela de pago del plan seleccionado.</p>
                <div class="list-card">
                    @foreach($planesMejora->take(3) as $planMejora)
                        <div class="item-row">
                            <strong>{{ $planMejora['nombre'] }}</strong>
                            <br><span class="muted">{{ $planMejora['descripcion'] }}</span>
                            <br><strong>${{ number_format($planMejora['valor_inicial'], 0, ',', '.') }}</strong>
                            <span class="muted"> inicio · ${{ number_format($planMejora['valor_mensual'], 0, ',', '.') }} mensual</span>
                            <br><a class="btn btn-success" style="margin-top:10px" href="{{ route('cliente.planes.pago', $planMejora['slug']) }}">Mejorar y pagar</a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @else
        <div class="plan-empty-alert" role="status">
            <x-icono nombre="suscripcion" />
            <div>
                <strong>Aún no tienes un plan activo</strong>
                <span>Elige una de las alternativas de abajo para comenzar.</span>
            </div>
        </div>
        <div class="plan-grid">
            @foreach($planesDisponibles as $planDisponible)
                <article class="plan-choice">
                    <span class="plan-badge">{{ $planDisponible['etiqueta'] }}</span>
                    <div>
                        <h2>{{ $planDisponible['nombre'] }}</h2>
                        <p>{{ $planDisponible['descripcion'] }}</p>
                    </div>
                    <div class="plan-price">
                        <div class="price-box">
                            <span>Inicio</span>
                            <strong>${{ number_format($planDisponible['valor_inicial'], 0, ',', '.') }}</strong>
                        </div>
                        <div class="price-box">
                            <span>Mensual</span>
                            <strong>${{ number_format($planDisponible['valor_mensual'], 0, ',', '.') }}</strong>
                        </div>
                    </div>
                    <ul class="plan-includes">
                        @foreach($planDisponible['incluye'] as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                    <a class="btn btn-success" href="{{ route('cliente.planes.pago', $planDisponible['slug']) }}">Contratar y pagar</a>
                </article>
            @endforeach
        </div>
    @endif
</section>

<section class="menu-lateral-seccion" id="cliente-mascotas" data-menu-panel="mascotas">
    <div class="section-head form-toggle-row">
        <div>
            <h2>Mis mascotas</h2>
            <p class="muted">Registra a tus mascotas para personalizar sus pedidos, planes y beneficios.</p>
        </div>
        <button type="button" class="btn-form-toggle" data-form-toggle="form-mascota" data-label-cerrado="Agregar mascota" aria-controls="form-mascota" aria-expanded="false"><x-icono nombre="plus" /><span data-toggle-label>Agregar mascota</span></button>
    </div>
    <div class="pets-layout">
        <div class="panel-card pets-form-card collapsible-form" id="form-mascota">
            <form method="POST" enctype="multipart/form-data" action="{{ route('cliente.mascotas.store') }}" data-keep-open="1">
                @csrf
                <h3 class="form-title">Datos de la mascota</h3>
                <div class="compact-form">
                    <div class="span-12">
                        <label class="floating-label-activo-sm">Acción</label>
                        <select class="form-control form-control-sm" name="mascota_id" id="mascota_id">
                            <option value="">Agregar nueva mascota</option>
                            @foreach($user->mascotas as $mascota)
                                <option value="{{ $mascota->id }}"
                                    data-nombre="{{ $mascota->nombre }}"
                                    data-especie="{{ $mascota->especie }}"
                                    data-raza="{{ $mascota->raza }}"
                                    data-sexo="{{ $mascota->sexo }}"
                                    data-color="{{ $mascota->color }}"
                                    data-peso="{{ $mascota->peso_kg }}"
                                    data-fecha="{{ optional($mascota->fecha_nacimiento)->toDateString() }}"
                                    data-chip="{{ $mascota->numero_chip }}"
                                    data-esterilizado="{{ $mascota->esterilizado ? '1' : '0' }}"
                                    data-alergias="{{ $mascota->alergias }}"
                                    data-observaciones="{{ $mascota->observaciones }}">{{ $mascota->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6"><label class="floating-label-activo-sm">Nombre mascota</label><input class="form-control form-control-sm" name="nombre" id="mascota_nombre" placeholder="Ej: Max, Luna, Pelusa" required></div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Especie</label>
                        <select class="form-control form-control-sm" name="especie" id="mascota_especie">
                            <option value="perro">Perro</option>
                            <option value="gato">Gato</option>
                            <option value="otro">Otro</option>
                        </select>
                    </div>
                    <div class="span-3"><label class="floating-label-activo-sm">Raza</label><input class="form-control form-control-sm" name="raza" id="mascota_raza" placeholder="Ej: Mestizo, Poodle"></div>
                    <div class="span-2">
                        <label class="floating-label-activo-sm">Sexo</label>
                        <select class="form-control form-control-sm" name="sexo" id="mascota_sexo">
                            <option value="">Seleccionar</option>
                            <option value="macho">Macho</option>
                            <option value="hembra">Hembra</option>
                            <option value="desconocido">Desconocido</option>
                        </select>
                    </div>
                    <div class="span-2"><label class="floating-label-activo-sm">Color</label><input class="form-control form-control-sm" name="color" id="mascota_color" placeholder="Ej: Café"></div>
                    <div class="span-2"><label class="floating-label-activo-sm">Peso kg</label><input class="form-control form-control-sm" type="number" name="peso_kg" id="mascota_peso" min="0" step="0.1" placeholder="Ej: 18"></div>
                    <div class="span-3"><label class="floating-label-activo-sm">Fecha nacimiento</label><input class="form-control form-control-sm" type="date" name="fecha_nacimiento" id="mascota_fecha"></div>
                    <div class="span-3"><label class="floating-label-activo-sm">Nro. chip</label><input class="form-control form-control-sm" name="numero_chip" id="mascota_chip" placeholder="Microchip si existe"></div>
                    <div class="span-6"><label class="floating-label-activo-sm">Foto mascota</label><input class="form-control form-control-sm" type="file" name="foto" accept="image/*"></div>
                    <div class="span-compact-check">
                        <label class="check-row"><input type="checkbox" name="esterilizado" id="mascota_esterilizado" value="1"> Esterilizado</label>
                    </div>
                    <div class="span-6"><label class="floating-label-activo-sm">Alergias / restricciones</label><textarea class="form-control form-control-sm" name="alergias" id="mascota_alergias" placeholder="Ej: alergia a pollo, dieta renal, medicamentos"></textarea></div>
                    <div class="span-6"><label class="floating-label-activo-sm">Observaciones de cuidado</label><textarea class="form-control form-control-sm" name="observaciones" id="mascota_observaciones" placeholder="Preferencias de alimento, conducta, cuidados especiales"></textarea></div>
                </div>
                <div class="card-form-actions">
                    <button type="button" class="btn btn-secondary" data-form-toggle-cerrar="form-mascota">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar mascota</button>
                </div>
            </form>
        </div>
        <div class="panel-card pets-list-card">
            <div class="list-card">
                @forelse($user->mascotas as $mascota)
                    <div class="item-row between">
                        <span class="item-main">
                            <span class="item-thumb">
                                @if($mascota->foto_url)
                                    <img src="{{ asset($mascota->foto_url) }}" alt="{{ $mascota->nombre }}" loading="lazy">
                                @else
                                    <x-icono nombre="mascota" />
                                @endif
                            </span>
                            <span>
                                <strong>{{ $mascota->nombre }}</strong>
                                <br><span class="muted">{{ ucfirst($mascota->especie) }}{{ $mascota->raza ? ' · ' . $mascota->raza : '' }}{{ $mascota->sexo ? ' · ' . ucfirst($mascota->sexo) : '' }}{{ $mascota->peso_kg ? ' · ' . $mascota->peso_kg . ' kg' : '' }}</span>
                                <br><span class="muted">Chip: {{ $mascota->numero_chip ?: 'Sin chip' }}{{ $mascota->esterilizado ? ' · Esterilizado' : '' }}</span>
                                @if($mascota->alergias)<br><span>Alergias: {{ $mascota->alergias }}</span>@endif
                                @if($mascota->observaciones)<br><span>{{ $mascota->observaciones }}</span>@endif
                            </span>
                        </span>
                        <span class="actions">
                            <button type="button" class="link-action" data-editar="{{ $mascota->id }}" data-editar-form="form-mascota" data-editar-select="mascota_id"><x-icono nombre="editar" /> Editar</button>
                        </span>
                    </div>
                @empty
                    <div class="empty-state">
                        <x-icono nombre="mascota" class="empty-state-icon" />
                        <strong>Aun no tienes mascotas</strong>
                        <span>Inscribe tu primera mascota para empezar.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-direcciones" data-menu-panel="direcciones">
    <div class="section-head form-toggle-row">
        <div>
            <h2>Direcciones de entrega</h2>
            <p class="muted">Guarda dónde quieres recibir tus pedidos y tus preferencias de entrega.</p>
        </div>
        <button type="button" class="btn-form-toggle" data-form-toggle="form-direccion" data-label-cerrado="Agregar dirección" aria-controls="form-direccion" aria-expanded="false"><x-icono nombre="plus" /><span data-toggle-label>Agregar dirección</span></button>
    </div>
    <div class="wide-section-layout">
        <div class="panel-card collapsible-form" id="form-direccion">
            <form method="POST" action="{{ route('cliente.direcciones.store') }}" data-keep-open="1">
                @csrf
                <h3 class="form-title">Datos de la dirección</h3>
                <div class="compact-form">
                    <div class="span-12">
                        <label class="floating-label-activo-sm">Acción</label>
                        <select class="form-control form-control-sm" name="direccion_id" id="direccion_id">
                            <option value="">Agregar nueva dirección</option>
                            @foreach($user->direcciones as $direccion)
                                <option value="{{ $direccion->id }}"
                                    data-alias="{{ $direccion->alias }}"
                                    data-direccion="{{ $direccion->direccion }}"
                                    data-region-id="{{ $direccion->region_id }}"
                                    data-comuna-id="{{ $direccion->comuna_id }}"
                                    data-referencia="{{ $direccion->referencia }}"
                                    data-dia="{{ $direccion->dia_preferencia }}"
                                    data-horario="{{ $direccion->horario_preferencia }}"
                                    data-pago="{{ $direccion->forma_pago_preferida }}"
                                    data-principal="{{ $direccion->principal ? '1' : '0' }}">{{ $direccion->alias }} - {{ $direccion->direccion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-4"><label class="floating-label-activo-sm">Nombre direccion</label><input class="form-control form-control-sm" name="alias" id="direccion_alias" value="Casa" placeholder="Ej: Casa, trabajo, parcela" required></div>
                    <div class="span-8"><label class="floating-label-activo-sm">Direccion despacho</label><input class="form-control form-control-sm" name="direccion" id="direccion_texto" value="{{ $user->direccion }}" placeholder="Calle, numero, depto o referencia principal" required></div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Región</label>
                        <select class="form-control form-control-sm" name="region_id" id="direccion_region" required>
                            <option value="">Seleccione una región</option>
                            @foreach($regionesVet as $region)
                                <option value="{{ $region->id }}">{{ $region->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Comuna</label>
                        <select class="form-control form-control-sm" name="comuna_id" id="direccion_comuna" required disabled>
                            <option value="">Seleccione primero una región</option>
                        </select>
                    </div>
                    <div class="span-12"><label class="floating-label-activo-sm">Referencia entrega</label><input class="form-control form-control-sm" name="referencia" id="direccion_referencia" placeholder="Ej: conserjería, portón azul, llamar antes"></div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm">Dia preferido</label>
                        <select class="form-control form-control-sm" name="dia_preferencia" id="direccion_dia">
                            <option value="">Sin preferencia</option>
                            @foreach(['lunes','martes','miercoles','jueves','viernes','sabado','domingo'] as $dia)
                                <option value="{{ $dia }}">{{ ucfirst($dia) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm">Horario preferido</label>
                        <select class="form-control form-control-sm" name="horario_preferencia" id="direccion_horario">
                            <option value="">Sin preferencia</option>
                            <option value="09:00 - 12:00">09:00 - 12:00</option>
                            <option value="12:00 - 15:00">12:00 - 15:00</option>
                            <option value="15:00 - 18:00">15:00 - 18:00</option>
                            <option value="18:00 - 21:00">18:00 - 21:00</option>
                        </select>
                    </div>
                    <div class="span-4">
                        <label class="floating-label-activo-sm">Forma de pago</label>
                        <select class="form-control form-control-sm" name="forma_pago_preferida" id="direccion_pago">
                            <option value="">Definir al pagar</option>
                            <option value="tarjeta">Tarjeta</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="efectivo">Efectivo</option>
                            <option value="pago_mensual">Cargo plan mensual</option>
                        </select>
                    </div>
                    <div class="span-12">
                        <label class="check-row"><input type="checkbox" name="principal" id="direccion_principal" value="1"> Usar como dirección principal</label>
                    </div>
                </div>
                <div class="card-form-actions">
                    <button type="button" class="btn btn-secondary" data-form-toggle-cerrar="form-direccion">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar dirección</button>
                </div>
            </form>
        </div>
        <div class="panel-card">
            <div class="list-card">
                @forelse($user->direcciones as $direccion)
                    <div class="item-row between">
                        <span class="item-main">
                            <span class="item-thumb item-thumb-redondo"><x-icono nombre="locacion" /></span>
                            <span>
                                <strong>{{ $direccion->alias }}</strong>
                                @if($direccion->principal)<span class="badge card-badge badge-default">Principal</span>@endif
                                <br><span class="muted">{{ $direccion->direccion }}</span>
                                @if($direccion->region || $direccion->comuna)
                                    <br><span class="muted">{{ collect([$direccion->comuna, $direccion->region])->filter()->implode(', ') }}</span>
                                @endif
                                @if($direccion->referencia)<br><span>{{ $direccion->referencia }}</span>@endif
                                @if($direccion->dia_preferencia || $direccion->horario_preferencia || $direccion->forma_pago_preferida)
                                    <br><span class="muted">{{ collect([
                                        $direccion->dia_preferencia ? 'Día: ' . ucfirst($direccion->dia_preferencia) : null,
                                        $direccion->horario_preferencia ? 'Horario: ' . $direccion->horario_preferencia : null,
                                        $direccion->forma_pago_preferida ? 'Pago: ' . str_replace('_', ' ', $direccion->forma_pago_preferida) : null,
                                    ])->filter()->implode(' · ') }}</span>
                                @endif
                            </span>
                        </span>
                        <span class="actions">
                            <button type="button" class="link-action" data-editar="{{ $direccion->id }}" data-editar-form="form-direccion" data-editar-select="direccion_id"><x-icono nombre="editar" /> Editar</button>
                            <form method="POST" action="{{ route('cliente.direcciones.destroy', $direccion) }}" class="inline-form" data-confirmar="¿Eliminar la dirección «{{ $direccion->alias }}»?">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="link-action danger"><x-icono nombre="eliminar" /> Eliminar</button>
                            </form>
                        </span>
                    </div>
                @empty
                    <div class="empty-state">
                        <x-icono nombre="locacion" class="empty-state-icon" />
                        <strong>Aún no tienes direcciones</strong>
                        <span>Agrega una dirección de entrega para tus pedidos.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-pedido" data-menu-panel="pedido">
    <div class="section-head form-toggle-row">
        <div>
            <h2>Pedidos programados</h2>
            <p class="muted">Recibe el alimento de tus mascotas automáticamente, con la frecuencia que elijas.</p>
        </div>
        <button type="button" class="btn-form-toggle" data-form-toggle="form-pedido" data-label-cerrado="Nuevo pedido" aria-controls="form-pedido" aria-expanded="false"><x-icono nombre="plus" /><span data-toggle-label>Nuevo pedido</span></button>
    </div>
    <div class="wide-section-layout">
        <div class="panel-card collapsible-form" id="form-pedido">
            <form method="POST" action="{{ route('cliente.planes.store') }}" data-keep-open="1">
                @csrf
                <h3 class="form-title">Datos del pedido</h3>
                <div class="compact-plan-form">
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Plan</label>
                        <select class="form-control form-control-sm" name="plan_id" id="plan_id">
                            <option value="">Nuevo plan</option>
                            @foreach($user->planesPedido->where('activo', true) as $plan)
                                <option value="{{ $plan->id }}">{{ $plan->producto->nombre }} - {{ $plan->frecuencia }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Mascota</label>
                        <select class="form-control form-control-sm" name="mascota_id" id="plan_mascota_id">
                            <option value="">Sin mascota específica</option>
                            @foreach($user->mascotas as $mascota)
                                <option value="{{ $mascota->id }}">{{ $mascota->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Alimento</label>
                        <select class="form-control form-control-sm" name="producto_id" id="plan_producto_id" required>
                            @foreach($productos->where('categoria', 'alimento_mascota') as $producto)
                                <option value="{{ $producto->id }}">{{ $producto->nombre }} - ${{ number_format($producto->precio, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Frecuencia</label>
                        <select class="form-control form-control-sm" name="frecuencia" id="plan_frecuencia">
                            <option value="mensual">Mensual</option>
                            <option value="semanal">Semanal</option>
                        </select>
                    </div>
                    <div class="span-3"><label class="floating-label-activo-sm">Cantidad</label><input class="form-control form-control-sm" type="number" name="cantidad" id="plan_cantidad" value="1" min="1" required></div>
                    <div class="span-6">
                        <label class="floating-label-activo-sm">Voucher</label>
                        <select class="form-control form-control-sm" name="voucher_descuento_id" id="plan_voucher_id">
                            <option value="">Sin voucher</option>
                            @foreach($vouchersPlan as $voucher)
                                <option value="{{ $voucher->id }}">{{ $voucher->codigo }} - {{ $voucher->titulo }} ({{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-3"><label class="floating-label-activo-sm">Próxima entrega</label><input class="form-control form-control-sm" type="date" name="proxima_entrega" id="plan_proxima_entrega" value="{{ now()->addWeek()->toDateString() }}" required></div>
                    <div class="span-3">
                        <label class="floating-label-activo-sm">Forma de pago</label>
                        <select class="form-control form-control-sm" name="forma_pago" id="plan_forma_pago">
                            <option value="">Usar forma de pago de la dirección</option>
                            <option value="tarjeta">Tarjeta inscrita</option>
                            <option value="cargo_mensual">Cargo mensual automático</option>
                            <option value="transferencia">Transferencia</option>
                            <option value="efectivo">Efectivo al recibir</option>
                        </select>
                    </div>
                    <div class="span-8">
                        <label class="floating-label-activo-sm">Dirección de entrega</label>
                        <select class="form-control form-control-sm" name="direccion_entrega" id="plan_direccion_entrega" required>
                            <option value="{{ $user->direccion }}">{{ $user->direccion ?: 'Dirección principal' }}</option>
                            @foreach($user->direcciones as $direccion)
                                <option value="{{ $direccion->direccion }}">{{ $direccion->alias }} - {{ $direccion->direccion }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="span-4">
                        <button type="button" class="btn btn-secondary payment-register" id="inscribir_tarjeta_btn" data-menu-ir="tarjetas">Inscribir tarjeta</button>
                    </div>
                </div>
                <div class="card-form-actions">
                    <button type="button" class="btn btn-secondary" data-form-toggle-cerrar="form-pedido">Cancelar</button>
                    <button type="submit" class="btn btn-success">Guardar pedido programado</button>
                </div>
            </form>
        </div>
        <div class="panel-card">
            <div class="list-card">
                @forelse($user->planesPedido->where('activo', true) as $plan)
                    <div class="item-row between">
                        <span class="item-main">
                            <span class="item-thumb"><x-icono nombre="carrito" /></span>
                            <span>
                                <strong>{{ $plan->producto->nombre }}</strong>
                                <br><span class="muted">{{ ucfirst($plan->frecuencia) }} · {{ $plan->cantidad }} {{ $plan->cantidad == 1 ? 'unidad' : 'unidades' }} · Próxima entrega {{ $plan->proxima_entrega->format('d-m-Y') }}</span>
                                <br><span>Mascota: {{ $plan->mascota?->nombre ?? 'General' }} · Voucher: {{ $plan->voucher?->codigo ?? 'Sin voucher' }}@if($plan->forma_pago) · Pago: {{ str_replace('_', ' ', $plan->forma_pago) }}@endif</span>
                                <br><span class="muted">{{ $plan->direccion_entrega }}</span>
                            </span>
                        </span>
                        <span class="actions">
                            <button type="button"
                                class="edit-plan-btn link-action"
                                data-id="{{ $plan->id }}"
                                data-mascota="{{ $plan->mascota_id }}"
                                data-producto="{{ $plan->producto_id }}"
                                data-voucher="{{ $plan->voucher_descuento_id }}"
                                data-frecuencia="{{ $plan->frecuencia }}"
                                data-cantidad="{{ $plan->cantidad }}"
                                data-proxima="{{ $plan->proxima_entrega->toDateString() }}"
                                data-direccion="{{ $plan->direccion_entrega }}"
                                data-pago="{{ $plan->forma_pago }}"><x-icono nombre="editar" /> Cambiar</button>
                            <form method="POST" action="{{ route('cliente.planes.anular', $plan) }}" class="inline-form" data-confirmar="¿Anular el pedido programado de {{ $plan->producto->nombre }}?">
                                @csrf
                                <button type="submit" class="btn-secondary link-action danger">Anular</button>
                            </form>
                        </span>
                    </div>
                @empty
                    <div class="empty-state">
                        <x-icono nombre="suscripcion" class="empty-state-icon" />
                        <strong>Aún no tienes pedidos programados</strong>
                        <span>Configura un pedido para recibir alimento automáticamente.</span>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-tracking" data-menu-panel="tracking">
    @php
        $pedidoTracking = $user->pedidos
            ->whereNotIn('estado', ['entregado', 'cancelado'])
            ->sortByDesc('created_at')
            ->first() ?? $user->pedidos->sortByDesc('created_at')->first();
        $ultimaUbicacion = $pedidoTracking?->tracking
            ? $pedidoTracking->tracking->whereNotNull('latitud')->whereNotNull('longitud')->sortByDesc('created_at')->first()
            : null;
        $eventosTracking = $pedidoTracking?->tracking ? $pedidoTracking->tracking->sortByDesc('created_at')->take(8) : collect();
        $repartidor = $pedidoTracking?->repartidor;
        $mapUrl = $ultimaUbicacion
            ? 'https://maps.google.com/maps?q=' . $ultimaUbicacion->latitud . ',' . $ultimaUbicacion->longitud . '&z=15&output=embed'
            : null;
    @endphp
    <div class="tracking-layout">
        <div class="panel-card">
            @php
                $secuencia = ['en_preparacion', 'listo_despacho', 'reparto_asignado', 'en_camino'];
                $estadoActual = ['preparando'=>'en_preparacion','asignado'=>'reparto_asignado','en_ruta'=>'en_camino'][$pedidoTracking?->estado] ?? $pedidoTracking?->estado;
                $posicion = array_search($estadoActual, $secuencia, true);
            @endphp
            <div class="tracking-steps">
                @foreach(['en_preparacion'=>'En preparación','listo_despacho'=>'Listo para despacho','reparto_asignado'=>'Reparto asignado','en_camino'=>'En camino'] as $estado=>$etiqueta)
                    @php $indice=array_search($estado,$secuencia,true); @endphp
                    <div class="tracking-step {{ $posicion !== false && $indice < $posicion ? 'done' : '' }} {{ $estadoActual === $estado ? 'current' : '' }}">
                        <span class="tracking-step-num">{{ $indice + 1 }}</span>
                        <span>{{ $etiqueta }}</span>
                    </div>
                @endforeach
            </div>
            <div class="between">
                <div>
                    <h2>Tracking del pedido</h2>
                    <p class="muted">
                        @if($pedidoTracking)
                            Pedido {{ $pedidoTracking->codigo_tracking }} · estado {{ str_replace('_', ' ', $pedidoTracking->estado) }}
                        @else
                            Aun no hay pedidos con tracking.
                        @endif
                    </p>
                </div>
                @if($pedidoTracking)
                    <a class="btn btn-success" href="{{ route('tracking.show', $pedidoTracking->codigo_tracking) }}">Abrir tracking completo</a>
                @endif
            </div>

            <div class="tracking-map">
                @if($mapUrl)
                    <iframe src="{{ $mapUrl }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Mapa tracking pedido"></iframe>
                @else
                    <span class="tracking-map-empty"><x-icono nombre="locacion" class="tracking-map-empty-icon" />Mapa pendiente: aun no hay ubicacion GPS enviada por el repartidor.</span>
                @endif
            </div>

            <div class="tracking-timeline">
                @forelse($eventosTracking as $evento)
                    <div class="tracking-event">
                        <strong>{{ ucfirst(str_replace('_', ' ', $evento->estado)) }}</strong>
                        <br><span>{{ $evento->mensaje }}</span>
                        <br><span class="muted">{{ $evento->created_at->format('d-m-Y H:i') }}</span>
                    </div>
                @empty
                    <div class="tracking-event">
                        <strong>Sin eventos registrados</strong>
                        <br><span class="muted">Cuando central o el repartidor actualicen el pedido, apareceran los movimientos aqui.</span>
                    </div>
                @endforelse
            </div>
        </div>

        <aside class="panel-card driver-card">
            <h2>Repartidor asignado</h2>
            @if($repartidor)
                @if($repartidor->foto_url)
                    <img class="driver-photo" src="{{ asset($repartidor->foto_url) }}" alt="{{ $repartidor->name }}">
                @endif
                <div class="vehicle-line"><strong>Nombre</strong><span>{{ $repartidor->name }}</span></div>
                <div class="vehicle-line"><strong>Telefono</strong><span>{{ $repartidor->telefono ?: 'No informado' }}</span></div>
                <div class="vehicle-line"><strong>Patente</strong><span>{{ $repartidor->vehiculo_patente ?: 'No informada' }}</span></div>
                <div class="vehicle-line"><strong>Vehiculo</strong><span>{{ trim(($repartidor->vehiculo_marca ?? '') . ' ' . ($repartidor->vehiculo_modelo ?? '')) ?: 'No informado' }}</span></div>
                @if($repartidor->vehiculo_foto_url)
                    <img class="driver-photo" src="{{ asset($repartidor->vehiculo_foto_url) }}" alt="Vehiculo {{ $repartidor->vehiculo_patente }}">
                @endif
            @else
                <div class="empty-state">
                    <x-icono nombre="usuario" class="empty-state-icon" />
                    <span>Aún no hay repartidor asignado. Te mostraremos sus datos cuando Central lo asigne.</span>
                </div>
            @endif
        </aside>
    </div>
</section>

<section class="menu-lateral-seccion" id="cliente-ofertas" data-menu-panel="ofertas">
    @php
        $ofertas = [
            [
                'titulo' => 'Alimentos recomendados',
                'texto' => 'Productos base para armar o complementar tu pedido recurrente.',
                'categoria' => 'alimento_mascota',
                'link' => route('tienda.catalogo', ['categoria' => 'alimento_mascota']),
                'items' => $productos->where('categoria', 'alimento_mascota')->take(4),
            ],
            [
                'titulo' => 'Farmacia y cuidados',
                'texto' => 'Antiparasitarios, suplementos, higiene y productos utiles para el cuidado diario.',
                'categoria' => 'medicamento',
                'link' => route('tienda.catalogo', ['categoria' => 'farmacia']),
                'items' => $productos->whereIn('categoria', ['medicamento', 'cuidado'])->take(4),
            ],
            [
                'titulo' => 'Servicios a domicilio',
                'texto' => 'Bano, peluqueria, veterinaria movil, paseos, hotel y otros servicios programables.',
                'categoria' => 'servicio',
                'link' => route('tienda.catalogo', ['categoria' => 'servicios']),
                'items' => $productos->whereIn('categoria', ['servicio', 'hotel', 'paseo_diario', 'cementerio'])->take(4),
            ],
            [
                'titulo' => 'Juguetes y entretencion',
                'texto' => 'Juguetes, mordedores y articulos para enriquecer la rutina de la mascota.',
                'categoria' => 'juguete',
                'link' => route('tienda.catalogo', ['categoria' => 'juguete']),
                'items' => $productos->where('categoria', 'juguete')->take(4),
            ],
            [
                'titulo' => 'Utiles para casa',
                'texto' => 'Platos, correas, dispensadores, higiene y accesorios para el dia a dia.',
                'categoria' => 'utensilio',
                'link' => route('tienda.catalogo', ['categoria' => 'utensilio']),
                'items' => $productos->whereIn('categoria', ['utensilio', 'cuidado'])->take(4),
            ],
            [
                'titulo' => 'Beneficios con voucher',
                'texto' => 'Descuentos disponibles para asociar a planes, compras o servicios.',
                'categoria' => 'voucher',
                'link' => route('tienda.catalogo', ['categoria' => 'adicional']),
                'items' => collect(),
            ],
        ];
    @endphp
    <div class="offers-grid">
        @foreach($ofertas as $oferta)
            <article class="offer-card">
                <span class="offer-badge">{{ $oferta['categoria'] }}</span>
                <div>
                    <h2>{{ $oferta['titulo'] }}</h2>
                    <p>{{ $oferta['texto'] }}</p>
                </div>

                @if($oferta['categoria'] === 'voucher')
                    <ul class="offer-list">
                        @forelse($vouchersPlan->take(4) as $voucher)
                            <li>
                                <span><strong>{{ $voucher->titulo }}</strong><br><span class="muted">{{ $voucher->codigo }}</span></span>
                                <span class="offer-price">{{ $voucher->tipo_descuento === 'porcentaje' ? $voucher->valor . '%' : '$' . number_format($voucher->valor, 0, ',', '.') }}</span>
                            </li>
                        @empty
                            <li><span class="muted">No hay vouchers disponibles por ahora.</span></li>
                        @endforelse
                    </ul>
                @else
                    <ul class="offer-list">
                        @forelse($oferta['items'] as $producto)
                            @php
                                $voucherProducto = $vouchersPlan
                                    ->filter(fn ($voucher) => ((int) $voucher->producto_id === (int) $producto->id || (!$voucher->producto_id && $voucher->categoria_aplicable === $producto->categoria)) && $producto->precio >= $voucher->monto_minimo)
                                    ->sortByDesc(function ($voucher) use ($producto) {
                                        return $voucher->tipo_descuento === 'porcentaje'
                                            ? (int) round($producto->precio * $voucher->valor / 100)
                                            : min($voucher->valor, $producto->precio);
                                    })
                                    ->first();
                                $descuentoProducto = $voucherProducto
                                    ? ($voucherProducto->tipo_descuento === 'porcentaje' ? (int) round($producto->precio * $voucherProducto->valor / 100) : min($voucherProducto->valor, $producto->precio))
                                    : 0;
                                $precioVoucher = max(0, $producto->precio - $descuentoProducto);
                            @endphp
                            <li @class(['has-voucher' => $voucherProducto])>
                                @if($voucherProducto)
                                    <span class="product-voucher-badge">% CON VOUCHER · {{ $voucherProducto->tipo_descuento === 'porcentaje' ? $voucherProducto->valor . '%' : '$' . number_format($voucherProducto->valor, 0, ',', '.') }}</span>
                                @endif
                                @php
                                    $fotoOferta = $producto->foto_url
                                        ? asset($producto->foto_url)
                                        : ($fotosReferencia[$producto->nombre] ?? null);
                                @endphp
                                <span class="item-thumb offer-thumb">
                                    @if($fotoOferta)
                                        <img src="{{ $fotoOferta }}" alt="{{ $producto->nombre }}" loading="lazy">
                                    @else
                                        <span>{{ strtoupper(substr($producto->categoria, 0, 3)) }}</span>
                                    @endif
                                </span>
                                <span class="offer-info"><strong>{{ $producto->nombre }}</strong><br><span class="muted">{{ $producto->marca }} {{ $producto->peso }}</span>@if($voucherProducto)<span class="voucher-code">Codigo: {{ $voucherProducto->codigo }}</span>@endif</span>
                                <span class="offer-side">
                                    @if($voucherProducto)
                                        <span class="old-offer-price">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                        <span class="offer-price discounted-offer-price">${{ number_format($precioVoucher, 0, ',', '.') }}</span>
                                    @else
                                        <span class="offer-price">${{ number_format($producto->precio, 0, ',', '.') }}</span>
                                    @endif
                                    <form class="offer-add" method="POST" action="{{ route('tienda.agregar', $producto) }}">
                                        @csrf
                                        <input type="hidden" name="cantidad" value="1">
                                        <button class="extra-btn" title="Agregar al carro" aria-label="Agregar {{ $producto->nombre }} al carro"><x-icono nombre="carrito" class="isdi-blanco" /></button>
                                    </form>
                                </span>
                            </li>
                        @empty
                            <li><span class="muted">Sin productos activos en esta categoria.</span></li>
                        @endforelse
                    </ul>
                @endif

                <a class="btn btn-orange-outline" href="{{ $oferta['link'] }}">Ver en tienda</a>
            </article>
        @endforeach
    </div>
</section>

</div>
</div>
</div>

<script>
    (function () {
        var botonTienda = document.querySelector('.carro-boton');
        var formularios = document.querySelectorAll('.offer-add');

        if (!formularios.length) {
            return;
        }

        function pintarContador(total) {
            if (!botonTienda) {
                return;
            }

            var contador = botonTienda.querySelector('.carro-contador');

            if (total > 0) {
                if (!contador) {
                    contador = document.createElement('span');
                    contador.className = 'carro-contador';
                    botonTienda.appendChild(contador);
                }

                contador.textContent = total;
                contador.classList.remove('is-nuevo');
                void contador.offsetWidth;
                contador.classList.add('is-nuevo');
                return;
            }

            if (contador) {
                contador.remove();
            }
        }

        formularios.forEach(function (formulario) {
            formulario.addEventListener('submit', function (evento) {
                evento.preventDefault();

                if (formulario.dataset.enviando === '1') {
                    return;
                }

                var boton = formulario.querySelector('.extra-btn');
                formulario.dataset.enviando = '1';
                boton.classList.add('is-cargando');

                fetch(formulario.action, {
                    method: 'POST',
                    body: new FormData(formulario),
                    credentials: 'same-origin',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (respuesta) {
                        if (!respuesta.ok) {
                            throw new Error(respuesta.status);
                        }
                        return respuesta.text();
                    })
                    .then(function (html) {
                        var doc = new DOMParser().parseFromString(html, 'text/html');
                        var nuevo = doc.querySelector('.carro-contador');
                        pintarContador(nuevo ? parseInt(nuevo.textContent, 10) || 0 : 0);

                        boton.classList.remove('is-cargando');
                        boton.classList.add('is-listo');
                        setTimeout(function () {
                            boton.classList.remove('is-listo');
                        }, 900);

                        formulario.dataset.enviando = '0';
                    })
                    .catch(function () {
                        boton.classList.remove('is-cargando');
                        formulario.dataset.enviando = '0';
                        formulario.submit();
                    });
            });
        });
    }());
    function bindSelectLoader(selectId, mapping) {
        var select = document.getElementById(selectId);
        if (!select) return;

        select.addEventListener('change', function () {
            var option = select.options[select.selectedIndex];
            Object.keys(mapping).forEach(function (key) {
                var field = document.getElementById(mapping[key]);
                if (!field) return;
                if (field.type === 'checkbox') {
                    field.checked = option.dataset[key] === '1';
                    return;
                }
                field.value = option.dataset[key] || '';
            });
        });
    }

    var planFormaPago = document.getElementById('plan_forma_pago');
    var inscribirTarjetaBtn = document.getElementById('inscribir_tarjeta_btn');
    if (planFormaPago && inscribirTarjetaBtn) {
        var toggleTarjeta = function () {
            inscribirTarjetaBtn.classList.toggle('is-visible', ['tarjeta', 'cargo_mensual'].indexOf(planFormaPago.value) >= 0);
        };
        planFormaPago.addEventListener('change', toggleTarjeta);
        toggleTarjeta();
    }

    function pintarToggle(button, abierto) {
        var texto = abierto ? (button.dataset.labelAbierto || 'Cerrar formulario') : (button.dataset.labelCerrado || 'Abrir formulario');
        var etiqueta = button.querySelector('[data-toggle-label]');
        (etiqueta || button).textContent = texto;
        button.setAttribute('aria-expanded', abierto ? 'true' : 'false');
    }

    function sincronizarToggles(id, abierto) {
        document.querySelectorAll('[data-form-toggle="' + id + '"]').forEach(function (toggle) {
            pintarToggle(toggle, abierto);
        });
    }

    // Abre un formulario desplegable, lo lleva a la vista y enfoca su primer campo.
    function abrirFormulario(id, enfocar) {
        var contenedor = document.getElementById(id);
        if (!contenedor) return null;
        contenedor.classList.add('is-open');
        sincronizarToggles(id, true);
        contenedor.scrollIntoView({behavior: 'smooth', block: 'start'});
        if (enfocar !== false) {
            var primero = contenedor.querySelector('input:not([type="hidden"]):not([type="radio"])');
            if (primero) primero.focus({preventScroll: true});
        }
        return contenedor;
    }

    function cerrarFormulario(id) {
        var contenedor = document.getElementById(id);
        if (!contenedor) return;
        contenedor.classList.remove('is-open');
        var formulario = contenedor.querySelector('form');
        if (formulario) {
            formulario.reset();
            formulario.querySelectorAll('select').forEach(function (select) {
                select.dispatchEvent(new Event('change'));
            });
            formulario.dispatchEvent(new Event('input'));
        }
        sincronizarToggles(id, false);
    }

    document.querySelectorAll('[data-form-toggle]').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = button.dataset.formToggle;
            var contenedor = document.getElementById(id);
            if (!contenedor) return;
            if (contenedor.classList.contains('is-open')) {
                cerrarFormulario(id);
            } else {
                abrirFormulario(id);
            }
        });
    });

    document.querySelectorAll('[data-form-toggle-cerrar]').forEach(function (button) {
        button.addEventListener('click', function () {
            var id = button.dataset.formToggleCerrar;
            cerrarFormulario(id);
            var toggle = document.querySelector('[data-form-toggle="' + id + '"]');
            if (toggle) toggle.focus();
        });
    });

    // Botones "Editar" de cada fila: abren el formulario con ese registro cargado.
    document.querySelectorAll('[data-editar]').forEach(function (boton) {
        boton.addEventListener('click', function () {
            var select = document.getElementById(boton.dataset.editarSelect);
            if (!abrirFormulario(boton.dataset.editarForm, false) || !select) return;
            select.value = boton.dataset.editar;
            select.dispatchEvent(new Event('change'));
        });
    });

    // Formularios con datos fijos que se habilitan con "Editar".
    document.querySelectorAll('[data-edit-toggle]').forEach(function (boton) {
        var formulario = document.getElementById(boton.dataset.editToggle);
        if (!formulario) return;
        var fieldset = formulario.querySelector('fieldset');

        function editar(activo) {
            formulario.classList.toggle('is-editing', activo);
            fieldset.disabled = !activo;
            boton.hidden = activo;
        }

        boton.addEventListener('click', function () {
            editar(true);
            var primero = fieldset.querySelector('input:not([type="hidden"])');
            if (primero) primero.focus();
        });

        formulario.querySelectorAll('[data-edit-cancel]').forEach(function (cancelar) {
            cancelar.addEventListener('click', function () {
                formulario.reset();
                formulario.querySelectorAll('.field-error').forEach(function (error) { error.remove(); });
                formulario.querySelectorAll('.has-error').forEach(function (campo) { campo.classList.remove('has-error'); });
                formulario.querySelectorAll('[data-pass-toggle][aria-pressed="true"]').forEach(function (ojo) { ojo.click(); });
                formulario.dispatchEvent(new Event('input'));
                editar(false);
                boton.focus();
            });
        });

        editar(formulario.classList.contains('is-editing'));
    });

    document.querySelectorAll('[data-pass-toggle]').forEach(function (ojo) {
        ojo.addEventListener('click', function () {
            var campo = document.getElementById(ojo.dataset.passToggle);
            if (!campo) return;
            var visible = campo.type === 'password';
            campo.type = visible ? 'text' : 'password';
            ojo.setAttribute('aria-pressed', visible ? 'true' : 'false');
            ojo.setAttribute('aria-label', visible ? 'Ocultar contraseña' : 'Mostrar contraseña');
        });
    });

    var formPassword = document.getElementById('form-password');
    if (formPassword) {
        var passNueva = document.getElementById('pass_password');
        var passRepetir = document.getElementById('pass_password_confirmation');
        var reglas = {
            largo: function () { return passNueva.value.length >= 8; },
            mezcla: function () { return /[A-Za-z]/.test(passNueva.value) && /\d/.test(passNueva.value); },
            coincide: function () { return passNueva.value !== '' && passNueva.value === passRepetir.value; }
        };
        formPassword.addEventListener('input', function () {
            Object.keys(reglas).forEach(function (regla) {
                var item = formPassword.querySelector('[data-regla="' + regla + '"]');
                if (item) item.classList.toggle('ok', reglas[regla]());
            });
        });
    }

    document.querySelectorAll('[data-solo-digitos]').forEach(function (campo) {
        campo.addEventListener('input', function () {
            campo.value = campo.value.replace(/\D/g, '').slice(0, 9);
        });
    });

    document.querySelectorAll('[data-rut]').forEach(function (campo) {
        campo.addEventListener('input', function () {
            var limpio = campo.value.replace(/[^0-9kK]/g, '').toUpperCase().slice(0, 9);
            if (limpio.length < 2) {
                campo.value = limpio;
                return;
            }
            var cuerpo = limpio.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            campo.value = cuerpo + '-' + limpio.slice(-1);
        });
    });

    document.querySelectorAll('form[data-confirmar]').forEach(function (formulario) {
        formulario.addEventListener('submit', function (evento) {
            if (!window.confirm(formulario.dataset.confirmar)) evento.preventDefault();
        });
    });

    document.querySelectorAll('[data-order-filter]').forEach(function (filtro) {
        filtro.addEventListener('click', function () {
            var grupo = filtro.dataset.orderFilter;
            document.querySelectorAll('[data-order-filter]').forEach(function (otro) {
                otro.classList.toggle('active', otro === filtro);
                otro.setAttribute('aria-pressed', otro === filtro ? 'true' : 'false');
            });
            document.querySelectorAll('.order-card').forEach(function (compra) {
                compra.hidden = grupo !== 'todas' && compra.dataset.orderGroup !== grupo;
            });
        });
    });

    // Formulario de tarjeta: formato del numero, marca detectada y vista previa.
    var formTarjeta = document.querySelector('#form-tarjeta form');
    if (formTarjeta) {
        var numeroTarjeta = document.getElementById('tarjeta_numero');
        var vencimientoTarjeta = document.getElementById('tarjeta_vencimiento');
        var titularTarjeta = document.getElementById('tarjeta_titular');
        var marcaTarjeta = document.getElementById('tarjeta_marca');
        var vistaTarjeta = document.getElementById('tarjeta_preview');
        var vista = function (clave) { return vistaTarjeta.querySelector('[data-preview="' + clave + '"]'); };
        var marcas = [
            [/^3[47]/, 'American Express', 'american-express'],
            [/^3(0[0-5]|[68])/, 'Diners Club', 'diners-club'],
            [/^4/, 'Visa', 'visa'],
            [/^(5[1-5]|2[2-7])/, 'Mastercard', 'mastercard'],
            [/^(50|5[6-9]|6)/, 'Maestro', 'maestro']
        ];

        function detectarMarca(digitos) {
            for (var i = 0; i < marcas.length; i++) {
                if (marcas[i][0].test(digitos)) return marcas[i];
            }
            return null;
        }

        numeroTarjeta.addEventListener('input', function () {
            var digitos = numeroTarjeta.value.replace(/\D/g, '').slice(0, 19);
            var marca = detectarMarca(digitos);
            var grupos = marca && marca[2] === 'american-express'
                ? [digitos.slice(0, 4), digitos.slice(4, 10), digitos.slice(10, 15)]
                : digitos.match(/.{1,4}/g) || [];
            numeroTarjeta.value = grupos.filter(Boolean).join(' ');
        });

        vencimientoTarjeta.addEventListener('input', function (evento) {
            var digitos = vencimientoTarjeta.value.replace(/\D/g, '').slice(0, 4);
            if (digitos.length === 1 && digitos > '1') digitos = '0' + digitos;
            var borrando = evento.inputType && evento.inputType.indexOf('delete') === 0;
            vencimientoTarjeta.value = digitos.length >= 2 && !(borrando && digitos.length === 2)
                ? digitos.slice(0, 2) + '/' + digitos.slice(2)
                : digitos;
        });

        formTarjeta.addEventListener('input', function () {
            var digitos = numeroTarjeta.value.replace(/\D/g, '');
            var marca = detectarMarca(digitos);
            var tipo = formTarjeta.querySelector('input[name="tipo"]:checked');
            marcaTarjeta.textContent = marca ? marca[1] : '';
            vistaTarjeta.className = 'card-visual' + (marca ? ' marca-' + marca[2] : '');
            vista('marca').textContent = marca ? marca[1] : 'Tarjeta';
            vista('tipo').textContent = tipo && tipo.value === 'debito' ? 'Débito' : 'Crédito';
            vista('numero').textContent = digitos ? '•••• •••• •••• ' + (digitos.slice(-4) + '••••').slice(0, 4) : '•••• •••• •••• ••••';
            vista('titular').textContent = titularTarjeta.value.trim().toUpperCase() || 'NOMBRE APELLIDO';
            vista('vencimiento').textContent = vencimientoTarjeta.value || 'MM/AA';
        });
        formTarjeta.dispatchEvent(new Event('input'));
    }

    document.querySelectorAll('.edit-plan-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            abrirFormulario('form-pedido', false);
            var setValue = function (id, value) {
                var field = document.getElementById(id);
                if (field) field.value = value || '';
            };

            setValue('plan_id', button.dataset.id);
            setValue('plan_mascota_id', button.dataset.mascota);
            setValue('plan_producto_id', button.dataset.producto);
            setValue('plan_voucher_id', button.dataset.voucher);
            setValue('plan_frecuencia', button.dataset.frecuencia);
            setValue('plan_cantidad', button.dataset.cantidad);
            setValue('plan_proxima_entrega', button.dataset.proxima);
            setValue('plan_direccion_entrega', button.dataset.direccion);
            setValue('plan_forma_pago', button.dataset.pago);
            if (planFormaPago) planFormaPago.dispatchEvent(new Event('change'));
        });
    });

    bindSelectLoader('mascota_id', {
        nombre: 'mascota_nombre',
        especie: 'mascota_especie',
        raza: 'mascota_raza',
        sexo: 'mascota_sexo',
        color: 'mascota_color',
        peso: 'mascota_peso',
        fecha: 'mascota_fecha',
        chip: 'mascota_chip',
        esterilizado: 'mascota_esterilizado',
        alergias: 'mascota_alergias',
        observaciones: 'mascota_observaciones'
    });

    var comunasVet = @json($comunasVet);
    var regionDireccion = document.getElementById('direccion_region');
    var comunaDireccion = document.getElementById('direccion_comuna');

    function cargarComunasDireccion(regionId, comunaSeleccionada) {
        if (!comunaDireccion) return;
        comunaDireccion.innerHTML = '<option value="">Seleccione una comuna</option>';
        (comunasVet || []).filter(function (comuna) {
            return String(comuna.id_region) === String(regionId);
        }).forEach(function (comuna) {
            var option = document.createElement('option');
            option.value = comuna.id;
            option.textContent = comuna.nombre;
            option.selected = String(comuna.id) === String(comunaSeleccionada || '');
            comunaDireccion.appendChild(option);
        });
        comunaDireccion.disabled = !regionId;
    }

    if (regionDireccion) {
        regionDireccion.addEventListener('change', function () {
            cargarComunasDireccion(regionDireccion.value, '');
        });
    }

    var direccionSelect = document.getElementById('direccion_id');
    if (direccionSelect) {
        direccionSelect.addEventListener('change', function () {
            var option = direccionSelect.options[direccionSelect.selectedIndex];
            var valores = {
                direccion_alias: option.dataset.alias || 'Casa',
                direccion_texto: option.dataset.direccion || '',
                direccion_referencia: option.dataset.referencia || '',
                direccion_dia: option.dataset.dia || '',
                direccion_horario: option.dataset.horario || '',
                direccion_pago: option.dataset.pago || ''
            };
            Object.keys(valores).forEach(function (id) {
                var campo = document.getElementById(id);
                if (campo) campo.value = valores[id];
            });
            document.getElementById('direccion_principal').checked = option.dataset.principal === '1';
            regionDireccion.value = option.dataset.regionId || '';
            cargarComunasDireccion(regionDireccion.value, option.dataset.comunaId || '');
        });
    }

    cargarComunasDireccion(regionDireccion ? regionDireccion.value : '', '');
</script>
@endsection
