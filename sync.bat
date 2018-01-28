
@echo off
d:

cd "D:\workspace\github"

echo "copy files to hytc1106hwc.github.io/web"

xcopy /Y /H /E "D:\workspace\github\my_blog" "D:\workspace\github\hytc1106hwc.github.io\web" 

pause