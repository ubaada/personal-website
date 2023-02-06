

# Custom CMS [Content Management System]

## Purpose
Allows admin to create, edit and delete posts with text and images.

## Structure
| File | Description |
| -------- | --------- |
| edit.php  | Creates, edits and deletes posts. Uses Summernote JS as the editor. |
| img_upload.php | Upload images while editing. |
| login.php |Signs in admin via session token creation. |
| session_auth | Checks if sesssion cookie is valid. |


## Permissions: 
Set up correct read write file permissions to 
	../data.db outside website root.
	this folder for creating folders
	For uploading images.
Set up SELinux permissions to files/folders above.

## Signup Default PW
Manually set up a user and their salted pw hash into the users table in the database. Use the following php function in CLI for the hash:
```
password_hash('MY_PASSWORD', HASHING_ALG);
```