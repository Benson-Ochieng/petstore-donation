(()=>{"use strict";const e=window.React,t=window.wp.element,n=window.givewp.form.templates,o=n.layouts.wrapper;const i=function(t,i="div",r){const{nodeType:a,type:s}=function(e){let t=null,o=null;return Object.entries(n).forEach((n=>{if("object"==typeof n[1]){const i=n[0],r=Object.keys(n[1]).find((t=>n[1][t]===e));r&&(o=r,t=i)}})),{nodeType:t,type:o}}(t);return n=>(0,e.createElement)(o,{nodeType:a,type:s,htmlTag:i,name:r},(0,e.createElement)(t,{...n}))}(window.givewp.form.templates.layouts.receipt),{receipt:r}=window.givewpDonationConfirmationReceiptExports,a=e=>"string"==typeof e?e:e?.amount?(e=>{const t=parseFloat(e),n=(o=r.settings.currency,i={},new Intl.NumberFormat(navigator.language,{style:"currency",currency:o,...i})).format(t);var o,i;const a=e.replace(/^[\d|.,]+/,"").trim();return a?`${n} ${a}`:n})(e.amount):JSON.stringify(e),s=e=>e?.map((({label:e,value:t})=>({label:e,value:a(t)})));function l(){return(0,e.createElement)(i,{heading:r.settings.heading,description:r.settings.description,donorDashboardUrl:r.settings.donorDashboardUrl,pdfReceiptLink:r.settings.pdfReceiptLink,donorDetails:r.donorDetails,donationDetails:s(r.donationDetails),subscriptionDetails:s(r.subscriptionDetails),eventTicketsDetails:s(r.eventTicketsDetails),additionalDetails:s(r.additionalDetails)})}const c=document.getElementById("root-givewp-donation-confirmation-receipt");t.createRoot?(0,t.createRoot)(c).render((0,e.createElement)(l,null)):(0,t.render)((0,e.createElement)(l,null),c),c.scrollIntoView({behavior:"smooth"})})();