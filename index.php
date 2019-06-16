<!DOCTYPE html>
<html>
<body>
	<form action="upload.php" method="post" enctype="multipart/form-data">
	    Select image to upload:
	    <input type="hidden" name="MAX_FILE_SIZE" value="30000" />
	    <input type="file" name="fileToUploadDpx" id="fileToUploadDpx">
	    <input type="submit" value="Upload Image" name="submit">
	</form>
</body>
</html>
