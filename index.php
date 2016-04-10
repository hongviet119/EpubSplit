<?php
if (isset($_POST['submit'])) {
    // Save uploaded file
    $originDir = dirname(__FILE__) . '/origin/';
    $targetFile = $originDir . basename($_FILES['epub']['name']);
    $fileType = pathinfo($targetFile,PATHINFO_EXTENSION);
    $errors = [];
    $success = null;
    // Check if file already exists
    if (file_exists($targetFile)) {
        $errors[] = "Sorry, file already exists.";
    }
    
    // Allow certain file formats
    if(! $fileType == 'epub') {
        $errors[] = "Sorry, you can only upload EPUB file.";
    }
    
    // Check if $uploadOk is set to 0 by an error
    if (! $errors) {
        if (! move_uploaded_file($_FILES['epub']['tmp_name'], $targetFile)) {
            $errors[] = "Sorry, there was an error uploading your file.";
        }
        else {
        
            // Define ebook-convert to run with full path here. It depends on your OS
            $command = 'xvfb-run /usr/bin/ebook-convert';

            $originPdf = str_replace('.epub', '.pdf', $targetFile);
            exec("$command $targetFile $originPdf");

            if (file_exists($originPdf)) {
                $previewPdf = str_replace(array('/origin/', '.pdf'), array('/preview/', '-preview.pdf'), $originPdf);
                $start = ! isset($_POST['start']) ? 1 : $_POST['start'];
                $pages = ! isset($_POST['pages']) ? 20 : $_POST['pages'];

                exec("/usr/bin/pdftk $originPdf cat $start-$pages output $previewPdf");
                unlink($originPdf);

                if (file_exists($previewPdf) && $_POST['output'] == 'epub') {
                    $preview = str_replace('.pdf', '.epub', $previewPdf);
                    exec("$command $previewPdf $preview");
                    unlink($previewPdf);
                }
                else {
                    $preview = $previewPdf;
                }
            }
            
            if (isset($preview)) {
                $namePieces = explode('/', $preview);
                $success = 'The file was processed successully. Please click <a href="preview/' . $namePieces[count($namePieces) - 1] . '">here</a> to view and download the preview one.';
            }
            else {
                $errors[] = 'There was an error while processing your EPUB.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>EPUB Splitter</title>
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css">
</head>
<body>
    <div class="container">
        <div class="page-header">
            <h1>EPUB Splitter</h1>
        </div>
        <?php if ($success):?>
        <div class="alert alert-success">
            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
            <?php echo $success?>
        </div>
        <?php endif;?>
        <div class="error-container">
            <?php if ($errors):?>
                <?php foreach ($errors as $error):?>
                    <div class="alert alert-danger">
                        <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                        <?php echo $error?>
                    </div>
                <?php endforeach;?>
            <?php endif;?>
        </div>
        <form action="index.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="epub">Select file to upload:</label>
                <input type="file" name="epub" class="form-control">
            </div>
            <div class="form-group">
                <label for="start">Start from:</label>
                <input type="number" name="start" class="form-control">
            </div>
            <div class="form-group">
                <label for="pages">Pages to be splitted:</label>
                <input type="number" name="pages" class="form-control">
            </div>
            <div class="form-group">
                <label for="output">Output format:</label>
                <select class="form-control" name="output">
                    <option value="pdf">PDF</option>
                    <option value="epub">EPUB</option>
                </select>
            </div>
            <input type="submit" value="Submit" name="submit" class="btn btn-success">
        </form>
    </div>
</body>
</html>