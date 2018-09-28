function pfizerWebstandardsCheckUrls(s) {
    "use strict";

    // lead_type_login
    var urls = [];
    urls.push('*/login');

    if (pfizerWebstandardsUrlMatch(urls)) {
        s.pfLead('login', 'initiated');
    }

}
