var socket = io.connect(phpvars.socket_address);

(function(win){

    socket.on('connect', function () {

        console.log('connected');
    
        // check for function
        if (typeof phpvars.method != 'undefined')
        {
            if (typeof win[phpvars.method] == 'function')
            {
                win[phpvars.method](socket);
            }
        }
    
        socket.on('disconnect', function () {
            console.log('disconnected');
        });
    });

})({
    // send local notification to counselor or volunteer
    newCaseAssigned : function(socket)
    {
        socket.emit("case assigned", phpvars.data);
    },

    // check and alert user when a new case has been sent
    checkForNewCases : function(socket)
    {
        // load notification
        document.query('#notification .default').then(audio => {

            function loadAudio(callback)
            {
                var audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                var source = audioCtx.createBufferSource();
                var xhr = new XMLHttpRequest();
                xhr.open('GET', audio.src);
                xhr.responseType = 'arraybuffer';
                xhr.addEventListener('load', function (r) {
                    audioCtx.decodeAudioData(
                    xhr.response, 
                    function (buffer) {
                        source.buffer = buffer;
                        source.connect(audioCtx.destination);
                        source.loop = false;
                    });
                    xhrSource = source;
                    callback.call(this, source);
                });
                xhr.send();
                
            }

            // panel timeout
            var panelTimeout = null;

            // stack them and fade out old notifications
            document.query('.slide-right-popup').then(slidePopup => {

                slidePopup.query('.text-box').then(textBox => {

                    socket.on('case submitted', function(caseType){
                
                        // play audio
                        loadAudio((source)=>{
                            source.start(0);
                        });
        
                        // clear timeout
                        clearTimeout(panelTimeout);
        
                        // update count 
                        document.queryAll('*[data-target="case-counter"]').then(counter => {
        
                            counter.forEach(count => {
                                
                                // get the inner count
                                const number = Number(count.innerText);
        
                                // update counter
                                count.innerText = number+1;
                            });
        
                            document.query('.stats-box-title.case-title').then(caseTitle => {
                                if (Number(counter[0].innerText) > 1) caseTitle.innerText = 'Reported Cases';
                            });
                        });
        
                        // update case today
                        document.query('*[data-target="case-today"]').then(caseToday => {
        
                            // get the number
                            const number = Number(caseToday.innerText.replace(/[^0-9]+/g,'')) + 1;
        
                            // add again
                            caseToday.innerText = '+' + number + ' today';
                        });
        
                        // show reload panel
                        document.query('.refresh-panel').then(panel => {
                            panel.style.bottom = '0';
                        });

                        // update txt box
                        textBox.innerText = 'You have a new ' + caseType + ' case submitted.';

                        // timeout
                        let timeout = 600;

                        // do we have the style attribute
                        if (!slidePopup.hasAttribute('data-slide')) timeout = 0;

                        // hide box
                        slidePopup.style.right = '-50%';

                        // show now
                        setTimeout(()=>{

                            // update url
                            let url = slidePopup.getAttribute('data-href');

                            // get target
                            let target = caseType == 'video' ? 'videos' : (caseType == 'audio' ? 'audios' : 'text-cases');

                            // update href
                            slidePopup.href = url + 'manager/cases?tab=' + target;

                            // show slide
                            slidePopup.style.right = '0%';

                            // set attribute
                            slidePopup.setAttribute('data-slide', 'yes');

                            // hide 
                            setTimeout(()=>{
                                slidePopup.style.right = '-50%';
                                slidePopup.removeAttribute('data-slide');
                            }, 15000);

                        },timeout);
                    });

                }); 
            });

        });
        
    }
})