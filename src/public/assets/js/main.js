// prototypes
const prototypes = Object.create(null);

// query prototype
prototypes.query = function(selector){
    return new Promise((resolve, rejected)=>{

        // make query
        const element = this.querySelector(selector);

        // are we good ?
        return (element !== null) ? resolve(element) : null;
    });
};

// query all prototype
prototypes.queryAll = function(selector){
    return new Promise((resolve, rejected)=>{

        // make query
        const element = this.querySelectorAll(selector);

        // are we good ?
        return (element.length > 0) ? resolve([].slice.call(element)) : null;
    });
};

// query prototype
Document.prototype.query = Element.prototype.query = prototypes.query;

// queryAll prototype
Document.prototype.queryAll = Element.prototype.queryAll = prototypes.queryAll;

// copy listener
const listener = Element.prototype.addEventListener, elementsWithListeners = [];

// listen for all listeners
Element.prototype.addEventListener = function(eventName, callback)
{
    //  push to listeners
    elementsWithListeners.push({
        element : this,
        eventName : eventName,
        callback : callback
    });

    // register
    this.on(eventName, callback);
};

// on listen
Element.prototype.on = function(eventName, callback)
{
   listener.apply(this, [eventName, callback]);
};

// manage preloaders
const preloaders = {
    spinner : {
        // spinner element
        spinnerElement : null,

        // show spinner
        show : function()
        {
            document.query('.spinner-loader').then(spinnerLoader => {

                // set display to flex
                spinnerLoader.style.display = 'flex';

                setTimeout(()=>{
                    // set opacity to 1
                    spinnerLoader.style.opacity = 1;
                }, 100);

                // ref element
                this.spinnerElement = spinnerLoader;
            });
        },

        // hide spinner
        hide : function()
        {
            if (this.spinnerElement !== null)
            {
                this.spinnerElement.style.opacity = 0;
                setTimeout(()=>{
                    // remove spinner style
                    this.spinnerElement.removeAttribute('style');
                },600);
            }
            else
            {
                document.query('.spinner-loader.custom').then(spinnerLoader => {
                    spinnerLoader.style.display = 'flex';
                    spinnerLoader.style.opacity = 1;

                    // remove class
                    spinnerLoader.classList.remove('custom');
                    this.spinnerElement = spinnerLoader;
                    this.hide();
                });
            }
        }
    },

    default : {
        // hide preloader
        hide : function()
        {
            document.query('.default-preloader').then(defaultPreloader => {
                // get the preloader container
                defaultPreloader.query('.preloader-container').then(preloaderContainer => {
                    
                    // get the progress bar
                    preloaderContainer.query('.preloader-progress').then(progress => {
                        preloaderContainer.query('.preloader-text').then( text => { text.style.opacity = 0; });
                        setTimeout(()=>{
                            progress.classList.add('loaded');
                            setTimeout(()=>{
                                preloaderContainer.style.opacity = 0;
                                defaultPreloader.style.opacity = 0;
                                setTimeout(()=>{
                                    defaultPreloader.style.display = 'none';
                                },600);
                            }, 700);
                        }, 1000);
                    });
                });
            });
        }
    }
};

// alert modals
const alerts = {
    modal : {
        successAlert : false,
        alertContainer : null,
        alertShowing : false,

        // show error alert
        show(message, callback = null)
        {
            // add container
            this.alertContainer = (this.alertContainer === null) ? document.querySelector('.alert-container') : this.alertContainer;

            // can we show ?
            if (this.alertContainer !== null && this.alertShowing === false)
            {
                // get the alert box
                const alertBox = this.alertContainer.querySelector('.alert-box > p');

                // replace content
                if (alertBox !== null) alertBox.innerHTML = message;

                // success alert??
                if (this.successAlert)
                {
                    this.alertContainer.classList.add('alert-success');
                    this.successAlert = false;
                }

                // show now
                this.alertContainer.style.display = 'block';

                //  make visible
                setTimeout(()=>{
                    this.alertContainer.style.opacity = 1;
                },100);

                // now showing
                this.alertShowing = true;

                // load hide
                this.hide(callback);
            }

            return null;
        },

        // success alert
        success(message, callback = null)
        {
            this.successAlert = true;
            return this.show(message, callback);
        },

        // hide alert
        hide(callback = null)
        {
            // add container
            this.alertContainer = (this.alertContainer === null) ? document.querySelector('.alert-container') : this.alertContainer;

            // check for auto display
            if (this.alertContainer.classList.contains('auto-show'))
            {
                if (this.alertContainer.hasAttribute('data-message'))
                {
                    // remove class
                    this.alertContainer.classList.remove('auto-show');

                    // show container
                    this.show(this.alertContainer.getAttribute('data-message'));
                }
            }

            //load button
            this.alertContainer.query('.alert-close-btn').then(alertCloseBtn => {

                const closeFunction = ()=>{
                    this.alertContainer.style.transform = 'translateY(-120vh)';
                    // not showing
                    this.alertShowing = false;

                    setTimeout(()=>{
                        this.alertContainer.removeAttribute('style');
                        this.alertContainer.className = 'alert-container';
                        // load callback
                        if (callback !== null && (typeof callback == 'function')) callback.call(this);
                    }, 700);
                };

                this.alertContainer.on('click', closeFunction);
                alertCloseBtn.addEventListener('click', closeFunction);

            });
        }
    }
};

// manage init calls
document.query('*[data-init]').then(init => {

    // get the function
    const func = init.getAttribute('data-init');
 
    // manage preloader if exists
    const preloader = {
 
         // get the preloader element
         preloaderElement : init.querySelector('.section-preloader'),
 
         // show preloader
         show : function()
         {
             // show preloader
             if (this.preloaderElement !== null) this.preloaderElement.style.display = 'block';
         },
 
         // hide preloader
         hide : function()
         {
             // hide preloader element
             if (this.preloaderElement !== null) this.preloaderElement.style.display = 'none';
         }
    };
 
    // check if function exists in the window
    if (typeof window[func] != 'undefined') window[func].call(this, init, preloader);
 });

// hide preloader
preloaders.default.hide();

// hide spinner
preloaders.spinner.hide();

// register hide for alert
alerts.modal.hide();

// manage menu toggle
document.query('.page-container-section').then((pageSection)=>{
    document.query('.page-container-menu').then((containerMenu)=>{
        document.query('*[data-target="menu"]').then(menuTrigger => {

            // hide menu
            const hideMenu = ()=>{
                pageSection.classList.add('show');
                containerMenu.classList.add('hide');
                menuTrigger.removeAttribute('data-clicked');

                setTimeout(()=>{
                    pageSection.classList.remove('hide');
                    containerMenu.classList.remove('show');
                },300);

                return null;
            };

            // when the container menu has been clicked
            // that's after clicking on the menu button.
            containerMenu.addEventListener('click', (ev)=>{
                if (ev.target.classList.contains('page-container-menu')) return hideMenu();
            });

            // when menu button has been clicked
            menuTrigger.addEventListener('click', ()=>{
                if (menuTrigger.hasAttribute('data-clicked')) return hideMenu();

                // show menu
                pageSection.classList.remove('show');
                containerMenu.classList.remove('hide');

                pageSection.classList.add('hide');
                containerMenu.classList.add('show');
                menuTrigger.setAttribute('data-clicked', 'yes');
                
            });
        });
    });
});

// manage page tabs
document.query('.page-tabs').then(pageTabs => {

    // look for data-target
    pageTabs.queryAll('*[data-target]').then(tabTarget => {

        // look data-target childs
        pageTabs.queryAll('*[data-tab]').then(targetTab => {

            // create event
            const eventName = document.createEvent('Event');

            // init event
            eventName.initEvent('tab-switched', true, true);

            // manage click for tab target
            tabTarget.forEach(target => {
                
                target.addEventListener('click', ()=>{
                    
                    // active already?
                    if (target.classList.contains('active')) return null;

                    // *******
                    // not active 
                    // *******

                    // remove active child
                    tabTarget.forEach(_target => {
                        if (_target.classList.contains('active'))
                        {
                            targetTab.forEach(tab => {
                                if (tab.getAttribute('data-tab') == _target.getAttribute('data-target')) tab.classList.remove('active');
                            });

                            // remove active tab
                            _target.classList.remove('active');
                        }
                    });

                    // set current tab target active
                    target.classList.add('active');

                    // set current tab active
                    targetTab.forEach(tab => {
                        if (tab.getAttribute('data-tab') == target.getAttribute('data-target')) tab.classList.add('active');
                    });

                    // set the target
                    eventName.targetTab = target;

                    // dispatch event
                    pageTabs.dispatchEvent(eventName);

                });
            });

        });
    });

});

// load stats-box-wrapper data-target
document.queryAll('.stats-box-wrapper *[data-target]').then(tabTarget => {
    // create event
    const eventName = document.createEvent('Event');

    // init event
    eventName.initEvent('tab-switched', true, true);

    // load all
    tabTarget.forEach(target => {

        // has been clicked
        target.on('click', ()=>{

            // load page-tabs target
            document.query('.page-tabs *[data-target="'+target.getAttribute('data-target')+'"]').then(mainTarget => {

                // load all listeners 
                elementsWithListeners.forEach(ele => {

                    // trigger callback
                    if (ele.element == mainTarget && ele.eventName == 'click') ele.callback();
                });
            });
        });
    });

    // read location
    setTimeout(function(){
        const location = window.location;
        if (location.search.indexOf('tab') == 1)
        {
            var tab = location.search.substr(5);

            // load page-tabs target
            document.query('.page-tabs *[data-target="'+tab+'"]').then(mainTarget => {

                // load all listeners 
                elementsWithListeners.forEach(ele => {

                    // trigger callback
                    if (ele.element == mainTarget && ele.eventName == 'click') ele.callback();
                });
            });
        }
    }, 100);
});

// manage delete modal trigger
document.query('.delete-modal').then(modal => {

    // hide modal function
    const hideModal = ()=>{
        modal.style.opacity = 0;
        setTimeout(() => { modal.style.display = 'none'; }, 600);
        modal.removeAttribute('data-clicked');
    };

    // hide modal if body of delete modal has been clicked
    modal.addEventListener('click', (ev)=>{
        if (ev.target.classList.contains('delete-modal')) return hideModal();
    });

    // hide modal if the cancel button has been clicked
    modal.query('button.cancel').then(button => {
        button.addEventListener('click', hideModal);
    });

    // trigger modal 
    document.queryAll('*[data-modal="delete"]').then(deleteModal => {

        // listen for click event
        deleteModal.forEach(dm => {
            dm.addEventListener('click', (ev)=>{
                if (!modal.hasAttribute('data-clicked'))
                {
                    ev.preventDefault();
                    modal.style.display = 'flex';
                    modal.setAttribute('data-clicked', 'yes');
                    setTimeout(()=>{ modal.style.opacity = 1;}, 200);

                    // track delete 
                    modal.query('button.delete').then(btnDelete => {

                        // get the input box
                        modal.query('.body input').then(inputBox => {

                            // get the form 
                            modal.query('form').then(formElement => {

                                // manage click event
                                btnDelete.on('click', (e)=>{
                                    e.preventDefault();

                                    // check inout value
                                    if (inputBox.value.toUpperCase().trim() == 'DELETE')
                                    {
                                        // does delete modal have data-method?
                                        if (dm.hasAttribute('data-method'))
                                        {
                                            formElement['REQUEST_METHOD'].value = dm.getAttribute('data-method');
                                        }

                                        // load all data-input
                                        if (dm.hasAttribute('data-input'))
                                        {
                                            const inputJson = JSON.parse(dm.getAttribute('data-input'));
                                            // add
                                            if (typeof inputJson == 'object')
                                            {
                                                modal.query('.input-elements').then(inputElementWrapper => {
                                                    inputElementWrapper.innerHTML = '';

                                                    for (var key in inputJson)
                                                    {
                                                        var input = document.createElement('input');
                                                        input.type = 'hidden';
                                                        input.name = key;
                                                        input.value = inputJson[key];

                                                        // add child
                                                        inputElementWrapper.appendChild(input);
                                                    }

                                                    // submit form
                                                    formElement.submit();
                                                });
                                                
                                            }
                                        }
                                    }
                                });

                            });
                            
                        });
                    });
                }
            });
        });
        
    });
});

// panel modal trigger
document.query('.panel-modal').then(panel => {

    // manage panel controls
    panel.query('.panel-controls').then(controls => {
        [].forEach.call(controls.children, button => {
            button.addEventListener('click', () => {
                // get the class name
                switch (button.className)
                {
                    // expand panel
                    case 'expand-btn':
                        // add fullscreen
                        if (!panel.classList.contains('fullscreen')) panel.classList.add('fullscreen');
                    break;

                    // minimize panel
                    case 'minimize-btn':
                        if (panel.classList.contains('fullscreen')) panel.classList.remove('fullscreen');
                    break;

                    // close panel
                    case 'close-btn':

                        // fade out
                        panel.style.opacity = 0;
                        panel.style.transform = 'translateX(20%)';
                    
                        // remove now
                        setTimeout(()=>{
                            panel.style.display = 'none';
                            panel.classList.remove('fullscreen');
                        }, 600);

                        // remove attribute
                        panel.removeAttribute('data-showing');

                    break;
                }
            });
        });
    });

    // look for data-panel
    document.queryAll('*[data-panel]').then(dataPanel => {
        // loop through
        dataPanel.forEach(trigger => {

            // listen for click event
            trigger.addEventListener('click', (ev)=>{

                ev.preventDefault();

                // button clicked
                if (!panel.hasAttribute('data-showing'))
                {
                    // set attribute
                    panel.setAttribute('data-showing', 'yes');

                    // manage which child to show
                    panel.query('.panel-body').then(panelBody => {
                        [].forEach.call(panelBody.children, panelBodyChild => {

                            // hide panels
                            panelBodyChild.style.display = 'none';

                            // show panel
                            if (panelBodyChild.hasAttribute('class') && panelBodyChild.classList.contains(trigger.getAttribute('data-panel')))
                            {
                                panelBodyChild.style.display = 'block';
                            }
                        });
                    });

                    // show panel
                    panel.style.display = 'block';
                    
                    // animate
                    setTimeout(()=>{
                        panel.style.opacity = 1;
                        panel.style.transform = 'translateX(0px)';
                        addToPreview();
                    }, 100);
                }
                
            });

        });
    });

});

// manage create input title
document.query('.create-input-title').then(inputTitle => {

    // store default text
    let defaultText = '';

    // manage default text
    if (typeof phpvars != 'undefined' && typeof phpvars.article_title)
    {
        // check the value
        if (phpvars.article_title != null)
        {
            defaultText = inputTitle.innerText;
            inputTitle.innerText = phpvars.article_title;
            inputTitle.classList.add('text-black');
        }
    }

    inputTitle.addEventListener('focus', (ev)=>{
        if (defaultText == '')
        {
            defaultText = inputTitle.innerText;
            inputTitle.innerText = '';

            // set text to be visible
            inputTitle.classList.add('text-black');
        }
    });

    if (inputTitle.hasAttribute('data-input-name'))
    {
        document.query('input[name="'+inputTitle.getAttribute('data-input-name')+'"]').then(input => {
            inputTitle.addEventListener('keyup', (ev)=>{
                
                // reset input value
                if (inputTitle.innerText.trim() == '') input.value = '';

                // update input
                input.value = inputTitle.innerText.trim();
            });
        });
    }

    inputTitle.addEventListener('blur', (ev)=>{
        if (inputTitle.innerText.trim() == '') 
        {
            inputTitle.innerText = defaultText;
            defaultText = '';
            inputTitle.classList.remove('text-black');
        }
    });
}); 

// try generate preview
document.query('#select_box').then(selectBox => {
    // do we have something
    if (selectBox.hasAttribute('name'))
    {
        // check for change event
        selectBox.addEventListener('change', ()=>{

            const file = selectBox.files[0];
            const type = file.type;

            if (type.match(/^(image)/))
            {
                var filereader = new FileReader();
                filereader.onload = (e) => {
                    selectBox.parentNode.style.backgroundImage = 'url("'+e.target.result+'")';

                    // look for data change
                    selectBox.parentNode.query('*[data-change]').then(ele => {
                        ele.innerText = ele.getAttribute('data-change');
                    });
                };
                filereader.readAsDataURL(file);
            }
            else if((type.match(/^(video)/)))
            {
                var filereader = new FileReader();
                filereader.file = file;
                filereader.onload = (e) => {

                    selectBox.parentNode.query('.video-preview-container').then(previewContainer => {

                        const previewParent = previewContainer.parentNode;
                        previewParent.removeChild(previewContainer);

                        // create container
                        let newPreviewContainer = document.createElement('video');
                        newPreviewContainer.className = 'video-preview-container';

                        // create source 
                        const source = document.createElement('source');
                        
                        source.src = e.target.result;
                        source.type = e.target.file.type;

                        // append
                        newPreviewContainer.appendChild(source);
                        newPreviewContainer.setAttribute('controls', 'yes');

                        // append to parent
                        previewParent.appendChild(newPreviewContainer);

                    });

                    // look for data change
                    selectBox.parentNode.query('*[data-change]').then(ele => {
                        ele.innerText = ele.getAttribute('data-change');
                    });
                };
                filereader.readAsDataURL(file);
            }
        });
    }
});

// manage dropdown
document.query('.user-dropdown').then(userDropdown => {
    // get the dropdown button
    userDropdown.query('.dropdown-button').then(dropdownButton => {

        // get dropdown ul
        userDropdown.parentNode.query('*[data-target="dropdown"]').then(dropdown => {

            // hide dropdown
            const hideDropdown = () => {
                dropdown.style.opacity = '0';
                dropdown.style.transform = 'translateY(20px)';
                dropdownButton.classList.remove('reverse');

                setTimeout(()=>{
                    dropdown.removeAttribute('style');
                    userDropdown.removeAttribute('data-clicked');
                },600);
            };

            // listen for click event
            userDropdown.addEventListener('click', () => {
                if (userDropdown.hasAttribute('data-clicked')) return hideDropdown();

                // reverse dropdown button
                dropdownButton.classList.add('reverse');

                // show dropdown
                userDropdown.setAttribute('data-clicked', 'yes');

                // manage visibility
                dropdown.style.display = 'flex';

                setTimeout(()=>{
                    dropdown.style.opacity = '1';
                    dropdown.style.transform = 'translateY(0px)';
                },100);
            });

        });

    });
});

// manage form input
document.queryAll('label.form-group').then((formGroup) => {

    formGroup.forEach((fm) => {

        // get the for attribute
        const forAttr = fm.getAttribute('for');

        // get the form element 
        fm.query('#'+forAttr+'').then(formElement => {

            // get span
            fm.query('span').then(span => {

                // manage input
                const manageInput = () => {

                    // check value
                    if (formElement.value == '') return span.classList.add('active-input');

                    // keep span up or move up
                    if (!span.classList.contains('active-input')) span.classList.add('active-input');
                };

                // manage form input on click
                fm.addEventListener('click', manageInput);

                // input element loses focus
                formElement.addEventListener('blur', ()=>{
                    if (formElement.value == '') return span.classList.remove('active-input');
                    // add 
                    manageInput();
                });

                formElement.addEventListener('change', ()=>{
                    if (formElement.value == '') return span.classList.remove('active-input');
                    // add 
                    manageInput();
                });

                // input element gains focus
                formElement.addEventListener('focus', ()=>{
                    if (!span.classList.contains('active-input')) span.classList.add('active-input');
                });

                // default behaviour
                if (formElement.value != '') return span.classList.add('active-input');
            });  
        });
    });
});

// manage body click event
document.query('.page-container').then(container => {
    setTimeout(()=>{
        document.query('.user-dropdown').then(userDropdown => {
            let userDropdownObject = null;
            let menuTriggerObject = null;

            // check container
            document.query('*[data-target="menu"]').then((menuTrigger)=>{
                // check list of elementsWithListeners
                elementsWithListeners.forEach((e) => {
                    if (e.element == userDropdown) userDropdownObject = e;
                    if (e.element == menuTrigger) menuTriggerObject = e;
                });
            });

            // container has been clicked
            container.on('click', (e) => {
                if (userDropdownObject !== null)
                {
                    if (userDropdownObject.eventName == 'click' && userDropdown.hasAttribute('data-clicked'))
                    {
                        if (e.target != userDropdown) userDropdownObject.callback.call();
                    }
                }

                if (menuTriggerObject !== null && !e.target.hasAttribute('data-clicked'))
                {
                    if (menuTriggerObject.eventName == 'click' && menuTriggerObject.element.hasAttribute('data-clicked'))
                    {
                        //e.preventDefault();
                        //menuTriggerObject.callback.call(this, {target : menuTriggerObject.element});
                        //preloaders.spinner.show();
                        
                        // setTimeout(()=>{
                        //     //e.target.click();
                        // },500); 

                        
                    }
                }
            });

        });
    },1000); 
});

// add to preview
function addToPreview()
{
    document.query('*[data-target="add-to-preview"]').then(ele => {

        // listen for change event
        ele.on('change', ()=>{
            
            const file = ele.files[0];
            const type = file.type;

            if (type.match(/^(image)/))
            {
                console.log('is an image', file);
                var filereader = new FileReader();
                filereader.onload = function(e){
                    document.query('.image-preview').then(preview => {
                        preview.firstElementChild.src = e.target.result;
                        preview.style.display = 'block';
                    });
                };
                filereader.readAsDataURL(file);
            }
        });
    });
}

// assign case
function assignCase(wrapper)
{
    // get assign-modal
    wrapper.query('.assign-modal').then(assignModal => {

        // modal processor
        const modalProcessor = {

            // show modal
            show : function(){
                assignModal.style.display = 'block';
                setTimeout(()=>{ assignModal.style.opacity = 1; 
                    assignModal.query('.container').then(container => {
                        container.style.opacity = 1;
                        container.style.transform = 'translateY(0px)';
                    });
                },100);
            },

            // hide modal
            hide : function(){
                assignModal.query('.container').then(container => {
                    container.style.opacity = 0;
                    container.style.transform = 'translateY(150px)';
                    setTimeout(()=>{
                        assignModal.style.opacity = 0;
                        setTimeout(()=>{assignModal.removeAttribute('style');}, 500);
                    }, 500);
                });
            }
        };

        // get the input element
        wrapper.query('[name="assign_to"]').then(inputElement => {
            // load data-accountid
            assignModal.queryAll('*[data-accountid]').then(accountBox => {
                // has it been clicked
                accountBox.forEach(a => {

                    // listen for click event
                    a.on('click', ()=>{
                        if (!a.classList.contains('active'))
                        {
                            // remove active box
                            assignModal.query('.account-box.active').then(activeBox => {
                                activeBox.classList.remove('active');
                            });

                            // add active
                            a.classList.add('active');

                            // update input element
                            inputElement.value = a.getAttribute('data-accountid');

                            // update assign-case text
                            wrapper.query('.assign-case .text').then(text => {
                                a.query('.account-info h2').then(info => {
                                    text.innerText = info.innerText;
                                });
                            });

                            // show the button
                            wrapper.query('.btn').then(btn => {
                                btn.style.display = 'flex';
                            });

                            // close modal
                            setTimeout(()=>{modalProcessor.hide();}, 100);
                        }
                    }); 
                });
            });
        });

        // show modal
        wrapper.query('.assign-case').then(assignCase => {
            assignCase.on('click', ()=>{
                modalProcessor.show();

                // hide modal
                wrapper.on('click', (e)=>{
                   if (e.target.classList.contains('account-lists') || e.target.classList.contains('assign-modal')) modalProcessor.hide();
                });
            });
        });

    });
}

// handle video previews
function handleVideoPreview()
{
    // manage video modal trigger
    document.query('*[data-tab="videos"]').then(video => {

        // load video modal
        document.query('.video-modal').then(modal => {

            // close modal function
            const closeModal = ()=>{

                // hide modal
                modal.style.opacity = 0;
                setTimeout(()=>{ modal.style.display = 'none'; },600);

                // remove listener
                modal.removeAttribute('data-modal-opened');

                // stop video
                modal.query('video').then(videoElement => {
                    videoElement.pause();
                    videoElement.currentTime = 0;
                });

                // default
                return null;
            };

            // look for the close button
            modal.query('.close-button').then(closeButton => {
                closeButton.addEventListener('click', closeModal);
            });

            // get all video tabs
            video.queryAll('*[data-trigger="modal"]').then(videos => {

                videos.forEach(ele => {

                    ele.addEventListener('click', ()=>{

                        // do we have listener
                        if (modal.hasAttribute('data-modal-opened')) return closeModal();

                        if (ele.hasAttribute('data-poster'))
                        {
                            // add modal listener
                            modal.setAttribute('data-modal-opened', 'yes');

                            // create new video file
                            const videoElement = document.createElement("video");
                            videoElement.setAttribute('controls', 'yes');
                            videoElement.setAttribute('poster', phpvars.config.storage + ele.getAttribute('data-poster'));
                            videoElement.innerHTML = '<source src="'+phpvars.config.storage + ele.getAttribute('data-mp4')+'" type="video/mp4"/>';

                            // add video
                            modal.query('video').then(oldVideoElement => {
                                oldVideoElement.parentNode.insertBefore(videoElement, oldVideoElement);
                                // Remove old
                                oldVideoElement.parentNode.removeChild(oldVideoElement);
                            })

                            // show modal
                            modal.style.display = 'flex';
                            setTimeout(()=>{ modal.style.opacity = 1; videoElement.play(); },200);
                        }
                        else
                        {
                            alerts.modal.show('Video file has been corrupted. Could not load video for preview');
                        }

                    });

                    modal.addEventListener('click', (ev)=>{
                        if (ev.target.classList.contains('video-modal')) return closeModal();
                    });
                });
            });
        });

    }); 
}

// load all data-href
function handleDataHref()
{
    // manage data-href click event
    document.queryAll('*[data-href]').then(ele => {
        ele.forEach(dataHref => {
            dataHref.on('click', (e)=>{
                if (e.target.nodeName != 'A' && e.target.nodeName != 'IMG' && !e.target.classList.contains('player')) window.open(dataHref.getAttribute('data-href'), '_self', 'location=yes');
            });
        });
    });
}

// load data href
handleDataHref();