/* ========================================
   FraudShield - Dashboard Scripts
   ======================================== */

// Toggle sidebar on mobile
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('open');
}

// Toggle notification dropdown
function toggleNotifDropdown(e) {
    e.stopPropagation();
    document.querySelector('.notif-dropdown').classList.toggle('show');
    var userDrop = document.querySelector('.user-dropdown');
    if (userDrop) userDrop.classList.remove('show');
}

// Toggle user dropdown
function toggleUserDropdown(e) {
    e.stopPropagation();
    document.querySelector('.user-dropdown').classList.toggle('show');
    var notifDrop = document.querySelector('.notif-dropdown');
    if (notifDrop) notifDrop.classList.remove('show');
}

// Close dropdowns when clicking outside
document.addEventListener('click', function(e) {
    var notifDrop = document.querySelector('.notif-dropdown');
    var userDrop = document.querySelector('.user-dropdown');
    if (notifDrop && !e.target.closest('.header-notifications')) {
        notifDrop.classList.remove('show');
    }
    if (userDrop && !e.target.closest('.header-user')) {
        userDrop.classList.remove('show');
    }
});

// Escape HTML to prevent XSS
function escapeHtml(str) {
    if (!str) return '';
    var div = document.createElement('div');
    div.appendChild(document.createTextNode(str));
    return div.innerHTML;
}

// Capitalize first letter
function capitalize(str) {
    if (!str) return '';
    return str.charAt(0).toUpperCase() + str.slice(1).toLowerCase();
}

// Format date for display
function formatDate(dateStr) {
    if (!dateStr) return '-';
    var d = new Date(dateStr);
    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    return months[d.getMonth()] + ' ' + d.getDate() + ', ' + d.getFullYear() + ' ' +
           String(d.getHours()).padStart(2,'0') + ':' + String(d.getMinutes()).padStart(2,'0');
}

// Format currency
function formatCurrency(amount) {
    var num = parseFloat(amount);
    if (isNaN(num)) return '$0.00';
    return '$' + num.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
}

// Get fraud score class
function getScoreClass(score) {
    score = parseInt(score);
    if (score >= 75) return 'high';
    if (score >= 40) return 'medium';
    return 'low';
}

// Get status badge class
function getStatusClass(status) {
    var map = {
        'approved': 'status-approved',
        'pending': 'status-pending',
        'flagged': 'status-flagged',
        'declined': 'status-declined',
        'reversed': 'status-reversed',
        'active': 'status-active',
        'frozen': 'status-frozen',
        'closed': 'status-closed',
        'inactive': 'status-inactive',
        'new': 'status-new',
        'investigating': 'status-investigating',
        'resolved': 'status-resolved',
        'dismissed': 'status-dismissed'
    };
    return map[status] || 'badge-outline';
}

// Get severity badge class
function getSeverityClass(severity) {
    var map = {
        'low': 'severity-low',
        'medium': 'severity-medium',
        'high': 'severity-high',
        'critical': 'severity-critical'
    };
    return map[severity] || 'badge-outline';
}

// Generate score bar HTML
function scoreBarHTML(score) {
    score = parseInt(score) || 0;
    var cls = getScoreClass(score);
    var colors = { low: '#10B981', medium: '#F59E0B', high: '#EF4444' };
    return '<div class="score-bar">' +
           '<div class="score-fill" style="width:' + score + '%;background:' + colors[cls] + ';height:6px;border-radius:3px;"></div>' +
           '<span style="color:' + colors[cls] + '">' + score + '</span>' +
           '</div>';
}

// Debounce utility
function debounce(func, wait) {
    var timeout;
    return function() {
        var ctx = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(function() { func.apply(ctx, args); }, wait);
    };
}

// AJAX helper
function fetchData(action, params, callback) {
    var url = 'fraudBackend.php?action=' + action;
    if (params) {
        for (var key in params) {
            url += '&' + encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
        }
    }
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var data = JSON.parse(xhr.responseText);
                callback(null, data);
            } catch(e) {
                callback('Parse error', null);
            }
        }
    };
    xhr.onerror = function() { callback('Network error', null); };
    xhr.send();
}

// POST AJAX helper
function postData(action, formData, callback) {
    var url = 'fraudBackend.php?action=' + action;
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function() {
        if (xhr.readyState === 4) {
            try {
                var data = JSON.parse(xhr.responseText);
                callback(null, data);
            } catch(e) {
                callback('Parse error', null);
            }
        }
    };
    xhr.onerror = function() { callback('Network error', null); };
    xhr.send(formData);
}
