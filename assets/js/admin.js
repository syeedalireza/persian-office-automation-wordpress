/**
 * Office Automation - Admin JavaScript
 * Modern interactions and animations
 */

(function($) {
    'use strict';

    const PersianOA = {
        init: function() {
            this.bindEvents();
            this.initAnimations();
            this.initDatePicker();
            this.initCirculationModal();
        },

        initCirculationModal: function() {
            if ($('#circulationModal').length === 0) {
                $('body').append(`
                    <div id="circulationModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:100000; align-items:center; justify-content:center;">
                        <div class="oa-card" style="width:90%; max-width:800px; max-height:90vh; overflow-y:auto; position:relative; margin: 20px auto;">
                            <div style="padding:20px; border-bottom:1px solid #eee; display:flex; justify-content:space-between; align-items:center;">
                                <h3 style="margin:0;">📊 نمودار گردش نامه</h3>
                                <button onclick="closeModal('circulationModal')" style="background:none; border:none; font-size:24px; cursor:pointer;">&times;</button>
                            </div>
                            <div id="circulationContent" style="padding:20px; text-align:center;">
                                <div class="oa-loading">در حال بارگذاری...</div>
                            </div>
                        </div>
                    </div>
                `);
            }
        },

        bindEvents: function() {
            // Modal controls
            window.openModal = function(id) {
                $('#' + id).fadeIn(300);
                $('body').css('overflow', 'hidden');
            };

            window.closeModal = function(id) {
                $('#' + id).fadeOut(300);
                $('body').css('overflow', '');
            };

            // Close modal on overlay click
            $(document).on('click', '[id$="Modal"]', function(e) {
                if ($(e.target).is('[id$="Modal"]')) {
                    $(this).fadeOut(300);
                    $('body').css('overflow', '');
                }
            });

            // Smooth scroll
            $('a[href^="#"]').on('click', function(e) {
                const href = $(this).attr('href');
                
                // Only handle valid ID selectors
                if (href && href.startsWith('#') && href.length > 1) {
                    e.preventDefault();
                    try {
                        const target = $(href);
                        if (target.length) {
                            $('html, body').animate({
                                scrollTop: target.offset().top - 100
                            }, 600);
                        }
                    } catch(err) {
                        // Ignore invalid selectors
                    }
                }
            });
        },

        initAnimations: function() {
            // Animate stats on scroll
            const observerOptions = {
                threshold: 0.2,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.oa-card, .oa-stat-card').forEach(function(card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px)';
                card.style.transition = 'all 0.6s cubic-bezier(0.16, 1, 0.3, 1)';
                observer.observe(card);
            });
        },

        // Initialize Persian Date Picker
        initDatePicker: function() {
            if (typeof $.fn.persianDatepicker === 'undefined') {
                console.warn('Persian Date Picker not loaded');
                return;
            }

            // Initialize all jalali datepickers
            $('.jalali-datepicker').each(function() {
                const $input = $(this);
                const targetId = $input.attr('id');
                const gregorianFieldId = targetId.replace('-jalali', '-gregorian');
                
                $input.persianDatepicker({
                    format: 'YYYY/MM/DD',
                    initialValue: $input.val() !== '' ? true : false,
                    initialValueType: 'persian',
                    autoClose: true,
                    calendar: {
                        persian: {
                            locale: 'fa',
                            showHint: true,
                            leapYearMode: 'algorithmic'
                        }
                    },
                    navigator: {
                        enabled: true,
                        scroll: {
                            enabled: true
                        },
                        text: {
                            btnNextText: '<',
                            btnPrevText: '>'
                        }
                    },
                    toolbox: {
                        enabled: true,
                        calendarSwitch: {
                            enabled: false
                        },
                        todayButton: {
                            enabled: true,
                            text: {
                                fa: 'امروز'
                            }
                        },
                        submitButton: {
                            enabled: true,
                            text: {
                                fa: 'تایید'
                            }
                        }
                    },
                    dayPicker: {
                        enabled: true,
                        titleFormat: 'YYYY MMMM'
                    },
                    timePicker: {
                        enabled: false
                    },
                    onSelect: function(unix) {
                        // Convert Persian date to Gregorian
                        if (typeof persianDate !== 'undefined') {
                            const pd = new persianDate(unix);
                            const gregorianDate = pd.toCalendar('gregorian').format('YYYY-MM-DD');
                            $('#' + gregorianFieldId).val(gregorianDate);
                        }
                    },
                    observer: true,
                    altField: '#' + gregorianFieldId,
                    altFormat: 'YYYY-MM-DD',
                    altFieldFormatter: function(unix) {
                        if (typeof persianDate !== 'undefined') {
                            const pd = new persianDate(unix);
                            return pd.toCalendar('gregorian').format('YYYY-MM-DD');
                        }
                        return '';
                    }
                });
            });

            // Legacy single datepicker support
            if ($('#jalali-datepicker').length && !$('#jalali-datepicker').hasClass('jalali-datepicker')) {
                $('#jalali-datepicker').persianDatepicker({
                    format: 'YYYY/MM/DD',
                    initialValue: true,
                    initialValueType: 'persian',
                    autoClose: true,
                    calendar: {
                        persian: {
                            locale: 'fa',
                            showHint: true,
                            leapYearMode: 'algorithmic'
                        }
                    },
                    navigator: {
                        enabled: true,
                        scroll: {
                            enabled: true
                        },
                        text: {
                            btnNextText: '<',
                            btnPrevText: '>'
                        }
                    },
                    toolbox: {
                        enabled: true,
                        calendarSwitch: {
                            enabled: false
                        },
                        todayButton: {
                            enabled: true,
                            text: {
                                fa: 'امروز'
                            }
                        },
                        submitButton: {
                            enabled: true,
                            text: {
                                fa: 'تایید'
                            }
                        }
                    },
                    dayPicker: {
                        enabled: true,
                        titleFormat: 'YYYY MMMM'
                    },
                    timePicker: {
                        enabled: false
                    },
                    onSelect: function(unix) {
                        // Convert Persian date to Gregorian
                        if (typeof persianDate !== 'undefined') {
                            const pd = new persianDate(unix);
                            const gregorianDate = pd.toCalendar('gregorian').format('YYYY-MM-DD');
                            $('#gregorian-date').val(gregorianDate);
                        }
                    },
                    observer: true,
                    altField: '#gregorian-date',
                    altFormat: 'YYYY-MM-DD',
                    altFieldFormatter: function(unix) {
                        if (typeof persianDate !== 'undefined') {
                            const pd = new persianDate(unix);
                            return pd.toCalendar('gregorian').format('YYYY-MM-DD');
                        }
                        return '';
                    }
                });
            }
        },

        // Notification system
        notify: function(message, type = 'success') {
            const colors = {
                success: '#10b981',
                error: '#ef4444',
                warning: '#f59e0b',
                info: '#3b82f6'
            };

            const notification = $('<div>')
                .css({
                    position: 'fixed',
                    top: '20px',
                    right: '20px',
                    padding: '16px 24px',
                    background: colors[type],
                    color: 'white',
                    borderRadius: '12px',
                    boxShadow: '0 10px 25px rgba(0,0,0,0.2)',
                    zIndex: 100001,
                    fontWeight: '600',
                    fontSize: '15px',
                    maxWidth: '400px',
                    animation: 'slideInRight 0.4s ease-out'
                })
                .text(message)
                .appendTo('body');

            setTimeout(function() {
                notification.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 4000);
        }
    };

    // Global view circulation function
    window.viewCirculation = function(id) {
        if (event) event.stopPropagation();
        
        openModal('circulationModal');
        $('#circulationContent').html('<div style="padding:40px;">در حال بارگذاری اطلاعات...</div>');
        
        // Fetch data
        $.post(persianOaData.ajaxUrl, {
            action: 'persian_oa_get_circulation_history',
            nonce: persianOaData.cartable_nonce,
            correspondence_id: id
        }, function(response) {
            if (response.success) {
                renderMermaidGraph(response.data.history);
            } else {
                $('#circulationContent').html('<div style="color:red; padding:20px;">' + (response.data.message || 'خطا در دریافت اطلاعات') + '</div>');
            }
        }).fail(function() {
            $('#circulationContent').html('<div style="color:red; padding:20px;">خطا در ارتباط با سرور</div>');
        });
    };

    function renderMermaidGraph(history) {
        if (!history || history.length === 0) {
            $('#circulationContent').html('<div style="padding:40px;">گردشی برای این نامه ثبت نشده است.</div>');
            return;
        }

        let graphDefinition = 'graph TD\n';
        graphDefinition += 'classDef default fill:#f9f9f9,stroke:#333,stroke-width:2px;\n';
        graphDefinition += 'classDef active fill:#e1f5fe,stroke:#01579b,stroke-width:2px;\n';
        
        // Build graph
        let nodes = new Set();
        let edges = [];
        
        history.forEach((item, index) => {
            const date = new Date(item.timestamp).toLocaleDateString('fa-IR');
            
            if (item.type === 'created') {
                const nodeId = `N${index}`;
                const label = `${item.user_name}<br/>${date}<br/>(ایجاد)`;
                graphDefinition += `${nodeId}["${label}"]\n`;
                nodes.add(nodeId);
            } else if (item.type === 'referral') {
                const sourceId = `U${item.user_id}`; // Simplified node ID logic might be needed
                const targetId = `U${item.target_id}`;
                
                // Using simple sequential nodes for better visualization of flow over time
                const fromNode = `N${index}_F`;
                const toNode = `N${index}_T`;
                
                // Better approach: Node per Step
                // We'll just list events in sequence
            }
        });

        // Re-thinking graph strategy:
        // Since referrals can go back and forth, a simple flow might be better represented by
        // User A -> User B -> User C
        
        // Let's build a sequence based graph
        // Node format: [Name]
        
        let graph = 'graph TD\n';
        graph += '%% Styles\n';
        graph += 'classDef start fill:#d1fae5,stroke:#059669,stroke-width:2px;\n';
        graph += 'classDef process fill:#eff6ff,stroke:#3b82f6,stroke-width:1px;\n';
        
        let prevNode = null;
        
        history.forEach((item, i) => {
            const date = new Date(item.timestamp).toLocaleDateString('fa-IR');
            const time = new Date(item.timestamp).toLocaleTimeString('fa-IR', {hour: '2-digit', minute:'2-digit'});
            const nodeId = `node_${i}`;
            
            let label = '';
            let style = 'process';
            
            if (item.type === 'created') {
                label = `<b>${item.user_name}</b><br/>ایجاد نامه<br/>${date} ${time}`;
                style = 'start';
                graph += `${nodeId}("${label}"):::${style}\n`;
                prevNode = nodeId;
            } else if (item.type === 'referral') {
                // If it's a referral, we show the sender (if not previous node) and receiver
                
                // But actually, the "flow" is from sender to receiver.
                // The previous node was the sender (or the one who held the letter).
                
                // Let's make it simpler: Each event is a transition.
                // But Mermaid needs Nodes and Edges.
                // Nodes = Users. Edges = Actions.
                
                // Unique User Nodes
                // This can get messy if A sends to B, B sends to A.
                // Flowchart TD handles it well.
                
                const senderId = `user_${item.user_id}`;
                const receiverId = `user_${item.target_id}`;
                
                // Add nodes definitions if not exist (Mermaid handles this implicitly but we can label them)
                // We use explicit labels for users to ensure they look good
                
                // We'll rely on implicit definitions for now to keep it simple, 
                // but we need to ensure the labels are set at least once.
                
                // Using distinct nodes for each STEP is better for a timeline view
                // UserA --[Referral]--> UserB
                
                const fromLabel = item.user_name.replace(/\s/g, '_');
                const toLabel = item.target_name.replace(/\s/g, '_');
                
                // To avoid cycles confusing the timeline, we can use subgraphs or just distinct IDs for each step.
                // Let's use Step IDs.
                
                const stepFrom = `S${i}_${item.user_id}`;
                const stepTo = `S${i}_${item.target_id}`;
                
                const actionLabel = `ارجاع: ${item.details || 'بدون توضیح'}<br/>${date} ${time}`;
                
                // graph += `${stepFrom}[${item.user_name}] -->|${actionLabel}| ${stepTo}[${item.target_name}]\n`;
                
                // Better: 
                // Created -> UserA
                // UserA -> UserB (Referral 1)
                
            }
        });
        
        // Final Attempt at Logic:
        // We will map the "Path" of the letter.
        // Node = User. Link = Action.
        // To show time progression, we'll keep it simple.
        
        let g = 'graph TD\n';
        g += 'classDef default fill:#fff,stroke:#333,stroke-width:1px,rx:5,ry:5;\n';
        
        let lastUserNode = null;
        
        history.forEach((item, i) => {
            const date = new Date(item.timestamp).toLocaleDateString('fa-IR');
            
            if (item.type === 'created') {
                const uId = `U${item.user_id}_${i}`; // Unique ID per step to avoid cycles
                g += `${uId}["👤 ${item.user_name}<br/><small>${date}</small>"]\n`;
                g += `${uId}:::default\n`;
                lastUserNode = uId;
                
                // Add start node
                g += `Start((شروع)) -->|ایجاد| ${uId}\n`;
            } 
            else if (item.type === 'referral') {
                const uTarget = `U${item.target_id}_${i}`;
                g += `${uTarget}["👤 ${item.target_name}<br/><small>${date}</small>"]\n`;
                
                if (lastUserNode) {
                    let desc = item.details ? (item.details.substring(0, 20) + (item.details.length>20?'...':'')) : 'ارجاع';
                    g += `${lastUserNode} -->|"${desc}"| ${uTarget}\n`;
                }
                lastUserNode = uTarget;
            }
            else if (item.type === 'response') {
                 const uTarget = `U${item.target_id}_${i}`;
                 g += `${uTarget}["👤 ${item.target_name}<br/><small>${date}</small>"]\n`;
                 
                 if (lastUserNode) {
                    let desc = item.details ? (item.details.substring(0, 20) + (item.details.length>20?'...':'')) : 'پاسخ';
                    g += `${lastUserNode} -.->|"${desc}"| ${uTarget}\n`; // Dotted line for response
                 }
                 lastUserNode = uTarget;
            }
        });

        $('#circulationContent').html('<div class="mermaid">' + g + '</div>');
        mermaid.init(undefined, $('.mermaid'));
    }

    // --- Settings Page Logic ---
    if ($('#oa-icon-preview').length) {
        window.handleIconPreview = function(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('oa_icon_preview');
                    preview.innerHTML = '<div style="position: relative; display: inline-block;"><img src="' + e.target.result + '" alt="پیش‌نمایش آیکون" style="width: 56px; height: 56px; object-fit: contain; border-radius: var(--oa-radius-lg); border: 2px solid var(--oa-gray-200); padding: 4px; background: white;"><button type="button" onclick="removeIcon()" style="position: absolute; top: -8px; right: -8px; background: var(--oa-danger); color: white; border: none; border-radius: 50%; width: 24px; height: 24px; cursor: pointer; font-size: 14px; line-height: 1;">×</button></div>';
                    document.getElementById('oa_title_icon_remove').value = '0';
                };
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.removeIcon = function() {
            document.getElementById('oa_icon_preview').innerHTML = '';
            document.getElementById('oa_title_icon').value = '';
            document.getElementById('oa_title_icon_remove').value = '1';
        };
    }

    if ($('#oa-categories-container').length) {
        window.addCategory = function() {
            const container = document.getElementById('oa-categories-container');
            const index = container.children.length + 1000;
            
            const div = document.createElement('div');
            div.className = 'oa-category-row';
            div.style.cssText = 'display: flex; gap: 12px; align-items: center;';
            div.innerHTML = `
                <input type="hidden" name="categories[${index}][key]" value="">
                <input type="text" name="categories[${index}][label]" class="oa-input" placeholder="عنوان دسته‌بندی جدید" required>
                <button type="button" class="oa-btn oa-btn-danger oa-btn-sm" onclick="this.parentElement.remove()" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
                    حذف
                </button>
            `;
            container.appendChild(div);
        };
    }

    // --- Workflow Builder Logic (Settings Page) ---
    // Only init if needed
    if ($('#workflow-form').length) {
        // Variables will be populated via wp_localize_script or inline (since we removed inline, we need to pass them)
        // But wait, the data (roles, workflows) is dynamic from PHP.
        // We should output it via wp_add_inline_script or localize_script in SettingsController.
        // Since I already removed the inline script block, I need to make sure the data is available.
        // The inline script I removed contained: `const availableRoles = ...; let currentWorkflows = ...;`
        
        // I should have kept the data definition but moved functions.
        // Or better: use wp_localize_script in SettingsController to pass this data.
        
        // Let's assume we will add wp_localize_script in SettingsController.php to pass 'persianOaWorkflowData'.
        
        let availableRoles = {};
        let currentWorkflows = [];
        
        if (typeof persianOaWorkflowData !== 'undefined') {
            availableRoles = persianOaWorkflowData.roles || {};
            currentWorkflows = persianOaWorkflowData.workflows || [];
        }

        window.openWorkflowModal = function(reset = true) {
            const modal = document.getElementById('workflowModal');
            if (reset) {
                document.getElementById('wf_id').value = '';
                document.getElementById('wf_name').value = '';
                document.getElementById('wf_description').value = '';
                document.getElementById('wf_sla').value = 24;
                document.getElementById('wf_active').checked = true;
                document.getElementById('wf_steps_container').innerHTML = '';
                addWorkflowStep(); 
            }
            modal.style.display = 'block';
        };

        window.closeWorkflowModal = function() {
            document.getElementById('workflowModal').style.display = 'none';
        };

        window.addWorkflowStep = function(data = null) {
            const container = document.getElementById('wf_steps_container');
            const stepIndex = container.children.length + 1;
            
            const div = document.createElement('div');
            div.className = 'step-item';
            div.innerHTML = `
                <div class="step-handle">⋮⋮</div>
                <div class="step-content">
                    <div>
                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">عنوان مرحله</label>
                        <input type="text" class="oa-input step-title" placeholder="عنوان مرحله" value="${data ? data.title : 'مرحله ' + stepIndex}">
                    </div>
                    <div>
                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">نوع تاییدکننده</label>
                        <select class="oa-select step-type" onchange="toggleStepValueInput(this)">
                            <option value="role" ${data && data.type === 'role' ? 'selected' : ''}>نقش کاربری (Role)</option>
                            <option value="user" ${data && data.type === 'user' ? 'selected' : ''}>کاربر خاص</option>
                            <option value="manager" ${data && data.type === 'manager' ? 'selected' : ''}>مدیر مستقیم</option>
                        </select>
                    </div>
                    <div>
                        <label style="font-size: 12px; display: block; margin-bottom: 4px;">انتخاب مقدار</label>
                        <div class="step-value-container"></div>
                    </div>
                </div>
                <button type="button" class="button button-small" onclick="this.parentElement.remove(); reorderSteps();" style="color: #b32d2e;">حذف</button>
            `;
            
            container.appendChild(div);
            const typeSelect = div.querySelector('.step-type');
            updateStepValueInput(typeSelect, data ? data.value : '');
        };

        window.updateStepValueInput = function(select, value = '') {
            const container = select.parentElement.parentElement.querySelector('.step-value-container');
            const type = select.value;
            
            if (type === 'role') {
                let options = '<option value="">انتخاب نقش...</option>';
                for (const [slug, role] of Object.entries(availableRoles)) {
                    const selected = value === slug ? 'selected' : '';
                    options += `<option value="${slug}" ${selected}>${role.name}</option>`;
                }
                container.innerHTML = `<select class="oa-select step-value">${options}</select>`;
            } else if (type === 'user') {
                container.innerHTML = `<input type="number" class="oa-input step-value" placeholder="ID کاربر" value="${value}">`;
            } else {
                container.innerHTML = `<input type="text" class="oa-input step-value" disabled value="خودکار" style="background: #eee;">`;
            }
        };

        window.toggleStepValueInput = function(select) {
            updateStepValueInput(select);
        };

        window.reorderSteps = function() {
            const container = document.getElementById('wf_steps_container');
            Array.from(container.children).forEach((child, index) => {
                const titleInput = child.querySelector('.step-title');
                if (titleInput.value.startsWith('مرحله ')) {
                    titleInput.value = 'مرحله ' + (index + 1);
                }
            });
        };

        window.editWorkflow = function(wf) {
            openWorkflowModal(false);
            document.getElementById('wf_id').value = wf.id;
            document.getElementById('wf_name').value = wf.name;
            document.getElementById('wf_description').value = wf.description || '';
            document.getElementById('wf_sla').value = wf.sla;
            document.getElementById('wf_active').checked = wf.is_active;
            
            const container = document.getElementById('wf_steps_container');
            container.innerHTML = '';
            
            if (wf.steps && wf.steps.length > 0) {
                wf.steps.forEach(step => addWorkflowStep(step));
            } else {
                addWorkflowStep();
            }
        };

        window.deleteWorkflow = function(id) {
            if (confirm('آیا از حذف این گردش کار اطمینان دارید؟')) {
                currentWorkflows = currentWorkflows.filter(w => w.id !== id);
                refreshWorkflowList();
            }
        };

        window.saveWorkflowToMemory = function() {
            const id = document.getElementById('wf_id').value || 'wf_' + Date.now();
            const name = document.getElementById('wf_name').value;
            if (!name) {
                alert('نام فرآیند الزامی است');
                return;
            }

            const steps = [];
            document.querySelectorAll('.step-item').forEach((item, index) => {
                const typeSelect = item.querySelector('.step-type');
                const valueInput = item.querySelector('.step-value');
                
                steps.push({
                    step: index + 1,
                    title: item.querySelector('.step-title').value,
                    type: typeSelect.value,
                    value: valueInput ? valueInput.value : ''
                });
            });

            const wf = {
                id: id,
                name: name,
                description: document.getElementById('wf_description').value,
                sla: parseInt(document.getElementById('wf_sla').value) || 24,
                is_active: document.getElementById('wf_active').checked,
                steps: steps
            };

            const existingIndex = currentWorkflows.findIndex(w => w.id === id);
            if (existingIndex >= 0) {
                currentWorkflows[existingIndex] = wf;
            } else {
                currentWorkflows.push(wf);
            }

            refreshWorkflowList();
            closeWorkflowModal();
        };

        window.refreshWorkflowList = function() {
            const tbody = document.getElementById('workflow-list-body');
            tbody.innerHTML = '';
            
            currentWorkflows.forEach(wf => {
                const status = wf.is_active ? 
                    '<span class="oa-badge oa-badge-success">فعال</span>' : 
                    '<span class="oa-badge oa-badge-danger">غیرفعال</span>';
                
                // Note: Using a template string with onclick passing object can be tricky with quotes.
                // Best to store data in a map or use data attributes.
                // But for now recreating exactly what was there.
                // We need to be careful with JSON.stringify inside the onclick attribute.
                // A safer way is to use data-id and attach event listener, or escape properly.
                // Since this is admin trusted area, basic escaping is ok, but single quotes in JSON will break it.
                // Let's use a global map or simply fetch from currentWorkflows by ID.
                
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td style="padding: 15px;"><strong>${wf.name}</strong></td>
                    <td style="padding: 15px;"><code>${wf.id}</code></td>
                    <td style="padding: 15px;">${wf.steps.length} مرحله</td>
                    <td style="padding: 15px;">${wf.sla}</td>
                    <td style="padding: 15px;">${status}</td>
                    <td style="padding: 15px; text-align: left;">
                        <button type="button" class="button edit-wf-btn" data-id="${wf.id}">ویرایش</button>
                        <button type="button" class="button button-link-delete" onclick="deleteWorkflow('${wf.id}')">حذف</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
            
            // Re-attach listeners for edit buttons
            $('.edit-wf-btn').click(function() {
                const id = $(this).data('id');
                const wf = currentWorkflows.find(w => w.id === id);
                if (wf) editWorkflow(wf);
            });
        };

        window.prepareWorkflowData = function() {
            document.getElementById('oa_workflow_definitions_input').value = JSON.stringify(currentWorkflows);
        };
        
        // Close modal when clicking outside
        $(window).click(function(event) {
            const modal = document.getElementById('workflowModal');
            if (event.target == modal) {
                modal.style.display = "none";
            }
        });
    }
    $(document).ready(function() {
        PersianOA.init();
        window.PersianOA = PersianOA;
    });

})(jQuery);

// Add CSS animations
const style = document.createElement('style');
style.textContent = `
    @keyframes slideInRight {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes scaleIn {
        from {
            opacity: 0;
            transform: scale(0.9);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
`;
document.head.appendChild(style);

