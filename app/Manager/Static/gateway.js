// create connection instance
const gateway = axios.create({
    baseURL : phpvars.config.endpoint,
    timeout : 1500,
    headers : phpvars.config.headers
});

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

// fetchCases function
function fetchCases(wrapper, preloader)
{
    // get all tabs
    wrapper.query('.tab-list *[data-target].active').then(activeTab => {

        // load processors
        const processors = {

            // render audio cases
            audios : function(jar)
            {
                gateway.get('cases/report/audio').then(response => 
                {
                    if (response.data.status == 'error')
                    {
                        // hide preloader
                        preloader.hide();

                        // show alerts
                        alerts.modal.show(response.data.message);
                    }
                    else
                    {
                        if (jar !== null)
                        {
                            // reset content
                            jar.innerHTML = '';

                            // load all
                            response.data.cases.forEach(caseObj => {

                                // create table row
                                let tr = document.createElement('tr');

                                // create audio data
                                var audio = document.createElement('audio');
                                if (caseObj.uploads.length > 0) audio.innerHTML = '<source src="'+(phpvars.config.storage + '/' + caseObj.uploads[0].audio_address)+'" type="audio/mp3"/>';
                                audio.style.display = 'none';

                                // create player area
                                var player = document.createElement('div');
                                player.className = 'player';
                                var playerImage = document.createElement('img');
                                playerImage.src = phpvars.config.images.play;
                                player.appendChild(playerImage);
                                player.appendChild(audio);

                                // play audio
                                player.on('click', ()=>{

                                    // pause others
                                    jar.queryAll('audio').then(aud => {
                                        aud.forEach(audi => {
                                            if (audi.parentNode.hasAttribute('data-played'))
                                            {
                                                audi.parentNode.query('img').then(img => {
                                                    img.src = phpvars.config.images.play;
                                                    img.removeAttribute('data-control');
                                                });
                                                audi.pause();
                                                audi.parentNode.removeAttribute('data-played');
                                            }
                                            else
                                            {
                                                if (audi.parentNode == player)
                                                {
                                                    // set attr
                                                    player.setAttribute('data-played', 'yes');

                                                    // set to pause image
                                                    playerImage.src = phpvars.config.images.pause;
                                                    playerImage.setAttribute('data-control', 'pause');

                                                    // play now
                                                    audi.play();

                                                    // audio ended
                                                    audi.on('ended', ()=>{
                                                        playerImage.removeAttribute('data-control');
                                                        playerImage.src = phpvars.config.images.play;
                                                        player.removeAttribute('data-played');
                                                    });
                                                }
                                            }
                                        });
                                    });
                                });

                                var td = document.createElement('td');
                                td.appendChild(player);

                                // add player
                                tr.appendChild(td);

                                // add row
                                this.addMediaToJar(jar, caseObj, 'audio-case', tr);
                            });

                            handleDataHref();
                        }

                        // hide preloader
                        preloader.hide();
                    }
                });
            },

            // render video cases
            videos : function(jar)
            {
                gateway.get('cases/report/video').then(response => 
                {
                    if (response.data.status == 'error')
                    {
                        // hide preloader
                        preloader.hide();

                        // show alerts
                        alerts.modal.show(response.data.message);
                    }
                    else
                    {
                        if (jar !== null)
                        {
                            // reset content
                            jar.innerHTML = '';

                            // load all
                            response.data.cases.forEach(caseObj => {

                                // create table row
                                let tr = document.createElement('tr');

                                // create player area
                                var player = document.createElement('div');
                                player.className = 'player';
                                var playerImage = document.createElement('img');
                                playerImage.src = phpvars.config.images.play;
                                player.appendChild(playerImage);
                                player.setAttribute('data-trigger', 'modal');

                                if (caseObj.uploads.length > 0)
                                {
                                    player.setAttribute('data-poster', caseObj.uploads[0].video_frame_address);
                                    player.setAttribute('data-mp4', caseObj.uploads[0].video_address);
                                }

                                var td = document.createElement('td');
                                td.appendChild(player);

                                // add player
                                tr.appendChild(td);

                                // add row
                                this.addMediaToJar(jar, caseObj, 'video-case', tr);
                            });

                            handleVideoPreview();
                            handleDataHref();
                        }

                        // hide preloader
                        preloader.hide();
                    }
                });
            },

            // render text cases
            'text-cases' : function(jar)
            {
                gateway.get('cases/report/text').then(response => 
                {
                    if (response.data.status == 'error')
                    {
                        // hide preloader
                        preloader.hide();

                        // show alerts
                        alerts.modal.show(response.data.message);
                    }
                    else
                    {
                        if (jar !== null)
                        {
                            // reset content
                            jar.innerHTML = '';

                            // load all
                            response.data.cases.forEach(caseObj => {

                                // create table row
                                let tr = document.createElement('tr');

                                // add row
                                this.addMediaToJar(jar, caseObj, 'text-case', tr);
                            });

                            handleDataHref();
                        }

                        // hide preloader
                        preloader.hide();
                    }
                });
            },

            // get jar container
            getJar : function(tab)
            {
                // Look for data-jar
                return wrapper.querySelector('*[data-jar="'+(tab.getAttribute('data-target'))+'"]');
            },

            // add media content to jar
            addMediaToJar : function(jar, caseObj, caseType, tr)
            {
                // add account info
                var a = document.createElement('a'), td = document.createElement('td'), p, span = document.createElement('span');

                // append account name
                if (caseObj.accountid == 0)
                {
                    td.appendChild(document.createTextNode(caseObj.account));
                    tr.appendChild(td);
                }
                else
                {
                    a.href = phpvars.config.url + 'manager/account-overview/' + caseObj.accountid;
                    a.classList.add('link');
                    a.style.textTransform = 'capitalize';
                    a.textContent = caseObj.account.lastname + ' ' + caseObj.account.firstname;
                    td.appendChild(a); 
                    // append
                    tr.appendChild(td);
                }

                // add date submitted
                td = document.createElement('td');
                td.appendChild(document.createTextNode(caseObj.date_formatted));
                tr.appendChild(td);

                // add case text
                td = document.createElement('td');
                p = document.createElement('p');
                p.classList.add('multiline-text');
                p.textContent = caseObj.case_text;
                td.appendChild(p);
                tr.appendChild(td);

                // add badge to span
                span.classList.add('table-badge');

                // check assigned to
                if (caseObj.assigned_to == '0')
                {
                    span.classList.add('pending');
                    span.textContent = 'No';
                }
                else
                {
                    span.classList.add('approved');
                    span.textContent = 'Yes';
                }

                // add assigned to
                td = document.createElement('td');
                td.appendChild(span);
                tr.appendChild(td);

                // add view button
                td = document.createElement('td');
                a = document.createElement('a');
                a.classList.add('view-btn');
                a.href = phpvars.config.url + 'manager/'+caseType+'/' + caseObj.casesreportedid;
                a.innerHTML = '<img src="'+phpvars.config.images.view+'"/>';
                tr.setAttribute('data-href', a.href);

                // append
                td.appendChild(a);
                tr.appendChild(td);

                // append line
                jar.appendChild(tr);
            }

        };

        // load processor
        processors[activeTab.getAttribute('data-target')](processors.getJar(activeTab));

        // when tab has been switched
        wrapper.on('tab-switched', (e) => {

            // load next active tab
            processors[e.targetTab.getAttribute('data-target')](processors.getJar(e.targetTab));

        });
    });
}