@extends('admin.layouts.app')

@section('title')
    Support Notification Settings
@stop

@section('content')
<div class="support-settings-container">
    <!-- Header -->
    <div class="settings-header">
        <div class="header-left">
            <h1>Support Notification Settings</h1>
            <p>Configure how and when support team members receive notifications</p>
        </div>
        <div class="header-right">
            <button class="btn btn-primary" onclick="saveAllSettings()">
                <i class="fas fa-save"></i> Save All Settings
            </button>
        </div>
    </div>

    <div class="settings-content">
        <!-- SMS Notification Settings -->
        <div class="settings-card">
            <div class="card-header">
                <div class="header-info">
                    <h2>
                        <i class="fas fa-sms"></i>
                        SMS Notification Settings
                    </h2>
                    <p>Configure SMS alerts for critical support events</p>
                </div>
                <div class="header-toggle">
                    <label class="switch">
                        <input type="checkbox" id="sms-enabled" checked>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Enable SMS</span>
                </div>
            </div>
            
            <div class="card-body">
                <div class="settings-grid">
                    <!-- Critical Ticket Alerts -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Critical Ticket Alerts</h4>
                            <p>Send SMS for high-priority tickets</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="sms-critical" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- New Ticket Alerts -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>New Ticket Alerts</h4>
                            <p>Notify when new tickets are created</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="sms-new-tickets">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Escalation Alerts -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Escalation Alerts</h4>
                            <p>Send SMS when tickets are escalated</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="sms-escalation" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- After Hours -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>After Hours</h4>
                            <p>SMS alerts outside business hours</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="sms-after-hours">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Phone Numbers -->
                <div class="phone-numbers-section">
                    <h4>Notification Phone Numbers</h4>
                    <div class="phone-list" id="phone-list">
                        <div class="phone-item">
                            <input type="tel" class="form-control" placeholder="+1234567890" value="+919876543210">
                            <button class="btn btn-danger btn-sm" onclick="removePhone(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="addPhoneNumber()">
                        <i class="fas fa-plus"></i> Add Phone Number
                    </button>
                </div>
            </div>
        </div>

        <!-- Email Notification Settings -->
        <div class="settings-card">
            <div class="card-header">
                <div class="header-info">
                    <h2>
                        <i class="fas fa-envelope"></i>
                        Email Notification Settings
                    </h2>
                    <p>Configure email notifications for support team</p>
                </div>
                <div class="header-toggle">
                    <label class="switch">
                        <input type="checkbox" id="email-enabled" checked>
                        <span class="slider"></span>
                    </label>
                    <span class="toggle-label">Enable Email</span>
                </div>
            </div>
            
            <div class="card-body">
                <div class="settings-grid">
                    <!-- Ticket Assignment -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Ticket Assignment</h4>
                            <p>Email when tickets are assigned to agents</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="email-assignment" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Ticket Status Changes -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Status Changes</h4>
                            <p>Notify when ticket status changes</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="email-status" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Daily Digest -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Daily Digest</h4>
                            <p>Send daily summary of support activities</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="email-digest" checked>
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Weekly Reports -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Weekly Reports</h4>
                            <p>Send weekly performance reports</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="email-weekly">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Email Recipients -->
                <div class="email-recipients-section">
                    <h4>Email Recipients</h4>
                    <div class="email-list" id="email-list">
                        <div class="email-item">
                            <input type="email" class="form-control" placeholder="email@example.com" value="support@asttrolok.com">
                            <select class="form-control">
                                <option>All Notifications</option>
                                <option>Critical Only</option>
                                <option>Digest Only</option>
                            </select>
                            <button class="btn btn-danger btn-sm" onclick="removeEmail(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                    <button class="btn btn-outline-primary btn-sm" onclick="addEmailRecipient()">
                        <i class="fas fa-plus"></i> Add Email Recipient
                    </button>
                </div>
            </div>
        </div>

        <!-- General Notification Settings -->
        <div class="settings-card">
            <div class="card-header">
                <div class="header-info">
                    <h2>
                        <i class="fas fa-cog"></i>
                        General Notification Settings
                    </h2>
                    <p>Global notification preferences and schedules</p>
                </div>
            </div>
            
            <div class="card-body">
                <div class="settings-grid">
                    <!-- Business Hours -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Business Hours</h4>
                            <p>Define support team working hours</p>
                        </div>
                        <div class="setting-control">
                            <div class="time-range">
                                <input type="time" id="business-hours-start" value="09:00">
                                <span>to</span>
                                <input type="time" id="business-hours-end" value="18:00">
                            </div>
                        </div>
                    </div>

                    <!-- Quiet Hours -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Quiet Hours</h4>
                            <p>Disable notifications during specific hours</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="quiet-hours-enabled">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Weekend Notifications -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Weekend Notifications</h4>
                            <p>Allow notifications on weekends</p>
                        </div>
                        <div class="setting-control">
                            <label class="switch">
                                <input type="checkbox" id="weekend-notifications">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <!-- Notification Frequency -->
                    <div class="setting-item">
                        <div class="setting-info">
                            <h4>Max Frequency</h4>
                            <p>Limit notifications per hour</p>
                        </div>
                        <div class="setting-control">
                            <select class="form-control" id="notification-frequency">
                                <option value="unlimited">Unlimited</option>
                                <option value="5">5 per hour</option>
                                <option value="10" selected>10 per hour</option>
                                <option value="20">20 per hour</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Priority Levels -->
                <div class="priority-section">
                    <h4>Priority Level Notifications</h4>
                    <div class="priority-grid">
                        <div class="priority-item">
                            <div class="priority-header">
                                <span class="priority-badge high">High</span>
                                <label class="switch">
                                    <input type="checkbox" id="priority-high" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <p>Critical issues, escalations, system outages</p>
                            <div class="notification-channels">
                                <label><input type="checkbox" checked> SMS</label>
                                <label><input type="checkbox" checked> Email</label>
                                <label><input type="checkbox" checked> In-App</label>
                            </div>
                        </div>

                        <div class="priority-item">
                            <div class="priority-header">
                                <span class="priority-badge medium">Medium</span>
                                <label class="switch">
                                    <input type="checkbox" id="priority-medium" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <p>Standard support requests, follow-ups</p>
                            <div class="notification-channels">
                                <label><input type="checkbox"> SMS</label>
                                <label><input type="checkbox" checked> Email</label>
                                <label><input type="checkbox" checked> In-App</label>
                            </div>
                        </div>

                        <div class="priority-item">
                            <div class="priority-header">
                                <span class="priority-badge low">Low</span>
                                <label class="switch">
                                    <input type="checkbox" id="priority-low">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <p>General inquiries, feedback, non-urgent</p>
                            <div class="notification-channels">
                                <label><input type="checkbox"> SMS</label>
                                <label><input type="checkbox"> Email</label>
                                <label><input type="checkbox" checked> In-App</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Settings Saved</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="success-message">
                        <i class="fas fa-check-circle text-success"></i>
                        <p>Your notification settings have been successfully saved.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.support-settings-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    background: #f8f9fa;
    min-height: 100vh;
}

.settings-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.header-left h1 {
    color: #2c3e50;
    margin-bottom: 0.5rem;
    font-size: 2rem;
    font-weight: 700;
}

.header-left p {
    color: #6c757d;
    margin: 0;
    font-size: 1.1rem;
}

.settings-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
    overflow: hidden;
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.header-info h2 {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0 0 0.5rem 0;
    font-size: 1.5rem;
    font-weight: 600;
}

.header-info p {
    margin: 0;
    opacity: 0.9;
    font-size: 0.9rem;
}

.header-toggle {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.toggle-label {
    font-size: 0.9rem;
    font-weight: 500;
}

.card-body {
    padding: 2rem;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.setting-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.setting-item:hover {
    border-color: #667eea;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
}

.setting-info h4 {
    color: #2c3e50;
    margin: 0 0 0.25rem 0;
    font-size: 1.1rem;
    font-weight: 600;
}

.setting-info p {
    color: #6c757d;
    margin: 0;
    font-size: 0.9rem;
}

/* Toggle Switch */
.switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .slider {
    background-color: #667eea;
}

input:checked + .slider:before {
    transform: translateX(26px);
}

/* Phone Numbers Section */
.phone-numbers-section,
.email-recipients-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.phone-numbers-section h4,
.email-recipients-section h4 {
    color: #2c3e50;
    margin-bottom: 1rem;
    font-weight: 600;
}

.phone-item,
.email-item {
    display: flex;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
    align-items: center;
}

.phone-item .form-control,
.email-item .form-control {
    flex: 1;
}

.email-item select {
    width: 200px;
}

/* Priority Section */
.priority-section {
    margin-top: 2rem;
    padding-top: 2rem;
    border-top: 1px solid #e9ecef;
}

.priority-section h4 {
    color: #2c3e50;
    margin-bottom: 1.5rem;
    font-weight: 600;
}

.priority-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.priority-item {
    padding: 1.5rem;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    background: #f8f9fa;
}

.priority-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
}

.priority-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
}

.priority-badge.high {
    background: #e74c3c;
}

.priority-badge.medium {
    background: #f39c12;
}

.priority-badge.low {
    background: #27ae60;
}

.priority-item p {
    color: #6c757d;
    margin-bottom: 1rem;
    font-size: 0.9rem;
}

.notification-channels {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.notification-channels label {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.9rem;
    color: #495057;
}

.time-range {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.time-range input {
    width: 120px;
}

.success-message {
    text-align: center;
    padding: 1rem;
}

.success-message i {
    font-size: 3rem;
    margin-bottom: 1rem;
    display: block;
}

.btn {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: #667eea;
    color: white;
}

.btn-primary:hover {
    background: #5a6fd8;
    transform: translateY(-1px);
}

.btn-outline-primary {
    background: transparent;
    color: #667eea;
    border: 1px solid #667eea;
}

.btn-outline-primary:hover {
    background: #667eea;
    color: white;
}

.btn-danger {
    background: #e74c3c;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.form-control {
    padding: 0.5rem;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 0.9rem;
}

.form-control:focus {
    outline: none;
    border-color: #667eea;
    box-shadow: 0 0 0 2px rgba(102, 126, 234, 0.2);
}

@media (max-width: 768px) {
    .settings-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .card-header {
        flex-direction: column;
        gap: 1rem;
        text-align: center;
    }
    
    .settings-grid {
        grid-template-columns: 1fr;
    }
    
    .priority-grid {
        grid-template-columns: 1fr;
    }
    
    .phone-item,
    .email-item {
        flex-direction: column;
        align-items: stretch;
    }
    
    .email-item select {
        width: 100%;
    }
}
</style>

<script>
// Add phone number
function addPhoneNumber() {
    const phoneList = document.getElementById('phone-list');
    const newPhoneItem = document.createElement('div');
    newPhoneItem.className = 'phone-item';
    newPhoneItem.innerHTML = `
        <input type="tel" class="form-control" placeholder="+1234567890">
        <button class="btn btn-danger btn-sm" onclick="removePhone(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    phoneList.appendChild(newPhoneItem);
}

// Remove phone number
function removePhone(button) {
    button.parentElement.remove();
}

// Add email recipient
function addEmailRecipient() {
    const emailList = document.getElementById('email-list');
    const newEmailItem = document.createElement('div');
    newEmailItem.className = 'email-item';
    newEmailItem.innerHTML = `
        <input type="email" class="form-control" placeholder="email@example.com">
        <select class="form-control">
            <option>All Notifications</option>
            <option>Critical Only</option>
            <option>Digest Only</option>
        </select>
        <button class="btn btn-danger btn-sm" onclick="removeEmail(this)">
            <i class="fas fa-trash"></i>
        </button>
    `;
    emailList.appendChild(newEmailItem);
}

// Remove email recipient
function removeEmail(button) {
    button.parentElement.remove();
}

// Save all settings
function saveAllSettings() {
    const settings = {
        sms: {
            enabled: document.getElementById('sms-enabled').checked,
            critical: document.getElementById('sms-critical').checked,
            newTickets: document.getElementById('sms-new-tickets').checked,
            escalation: document.getElementById('sms-escalation').checked,
            afterHours: document.getElementById('sms-after-hours').checked,
            phoneNumbers: Array.from(document.querySelectorAll('#phone-list input[type="tel"]'))
                .map(input => input.value)
                .filter(value => value.trim() !== '')
        },
        email: {
            enabled: document.getElementById('email-enabled').checked,
            assignment: document.getElementById('email-assignment').checked,
            status: document.getElementById('email-status').checked,
            digest: document.getElementById('email-digest').checked,
            weekly: document.getElementById('email-weekly').checked,
            recipients: Array.from(document.querySelectorAll('#email-list .email-item')).map(item => ({
                email: item.querySelector('input[type="email"]').value,
                type: item.querySelector('select').value
            })).filter(recipient => recipient.email.trim() !== '')
        },
        general: {
            businessHoursStart: document.getElementById('business-hours-start').value,
            businessHoursEnd: document.getElementById('business-hours-end').value,
            quietHoursEnabled: document.getElementById('quiet-hours-enabled').checked,
            weekendNotifications: document.getElementById('weekend-notifications').checked,
            maxFrequency: document.getElementById('notification-frequency').value,
            priorityHigh: document.getElementById('priority-high').checked,
            priorityMedium: document.getElementById('priority-medium').checked,
            priorityLow: document.getElementById('priority-low').checked
        }
    };

    // Save to backend
    fetch('/admin/support-settings/save', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success modal
            const modal = new bootstrap.Modal(document.getElementById('successModal'));
            modal.show();
        } else {
            alert('Error saving settings: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error saving settings:', error);
        alert('Error saving settings');
    });
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Load existing settings
    loadSettings();
});

function loadSettings() {
    fetch('/admin/support-settings/load')
        .then(response => response.json())
        .then(data => {
            if (data.settings) {
                const settings = data.settings;
                
                // SMS settings
                document.getElementById('sms-enabled').checked = settings.sms?.enabled || false;
                document.getElementById('sms-critical').checked = settings.sms?.critical || false;
                document.getElementById('sms-new-tickets').checked = settings.sms?.newTickets || false;
                document.getElementById('sms-escalation').checked = settings.sms?.escalation || false;
                document.getElementById('sms-after-hours').checked = settings.sms?.afterHours || false;
                
                // Email settings
                document.getElementById('email-enabled').checked = settings.email?.enabled || false;
                document.getElementById('email-assignment').checked = settings.email?.assignment || false;
                document.getElementById('email-status').checked = settings.email?.status || false;
                document.getElementById('email-digest').checked = settings.email?.digest || false;
                document.getElementById('email-weekly').checked = settings.email?.weekly || false;
                
                // General settings
                document.getElementById('business-hours-start').value = settings.general?.businessHoursStart || '09:00';
                document.getElementById('business-hours-end').value = settings.general?.businessHoursEnd || '18:00';
                document.getElementById('quiet-hours-enabled').checked = settings.general?.quietHoursEnabled || false;
                document.getElementById('weekend-notifications').checked = settings.general?.weekendNotifications || false;
                document.getElementById('notification-frequency').value = settings.general?.maxFrequency || '10';
                document.getElementById('priority-high').checked = settings.general?.priorityHigh || false;
                document.getElementById('priority-medium').checked = settings.general?.priorityMedium || false;
                document.getElementById('priority-low').checked = settings.general?.priorityLow || false;
                
                // Load phone numbers
                if (settings.sms?.phoneNumbers && settings.sms.phoneNumbers.length > 0) {
                    const phoneList = document.getElementById('phone-list');
                    phoneList.innerHTML = '';
                    settings.sms.phoneNumbers.forEach(phone => {
                        const phoneItem = document.createElement('div');
                        phoneItem.className = 'phone-item';
                        phoneItem.innerHTML = `
                            <input type="tel" class="form-control" value="${phone}">
                            <button class="btn btn-danger btn-sm" onclick="removePhone(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                        phoneList.appendChild(phoneItem);
                    });
                }
                
                // Load email recipients
                if (settings.email?.recipients && settings.email.recipients.length > 0) {
                    const emailList = document.getElementById('email-list');
                    emailList.innerHTML = '';
                    settings.email.recipients.forEach(recipient => {
                        const emailItem = document.createElement('div');
                        emailItem.className = 'email-item';
                        emailItem.innerHTML = `
                            <input type="email" class="form-control" value="${recipient.email}">
                            <select class="form-control">
                                <option ${recipient.type === 'All Notifications' ? 'selected' : ''}>All Notifications</option>
                                <option ${recipient.type === 'Critical Only' ? 'selected' : ''}>Critical Only</option>
                                <option ${recipient.type === 'Digest Only' ? 'selected' : ''}>Digest Only</option>
                            </select>
                            <button class="btn btn-danger btn-sm" onclick="removeEmail(this)">
                                <i class="fas fa-trash"></i>
                            </button>
                        `;
                        emailList.appendChild(emailItem);
                    });
                }
            }
        })
        .catch(error => {
            console.error('Error loading settings:', error);
        });
}
</script>
@stop
