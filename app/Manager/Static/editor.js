// @var array
const pictures = [];

// get the content area
document.query('.content-area').then(ca => {

    // @var editor
    $('.summernote').summernote({
        height: (ca.offsetHeight - 80),
        focus : true,
        lineHeights: ['0.2', '0.3', '0.4', '0.5', '0.6', '0.8', '1.0', '1.2', '1.4', '1.5', '2.0', '3.0'],
        toolbar: [
            ['style', ['style']],
            ['font', ['bold', 'underline', 'clear']],
            ['fontname', ['fontname']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture']],
            ['view', ['codeview', 'help']],
        ],
        callbacks : {
            onImageUpload : function(files)
            {
                const fileLength = files.length;
                const summer = $('.summernote');

                for (var x = 0; x < fileLength; x++)
                {
                    var filereader = new FileReader();
                    filereader.file = files[x];
                    filereader.onloadend = (e) => {
                        var imgNode = document.createElement('img');
                        imgNode.src = e.target.result;
                        imgNode.setAttribute('data-src', e.target.file.name);
                        summer.summernote('insertNode', imgNode);

                        // push for upload
                        pictures.push({
                            name : e.target.file.name,
                            target : 'data-src',
                            file : e.target.file
                        });
                    };
                    filereader.readAsDataURL(files[x]);
                }
            }
        }
    });

});
