Increment Number Field
------------------------------------

Version: 1.1
Author: Nick Dunn (nick.dunn@airlock.com)
Build Date: 2008-12-19
Requirements: Symphony 2.0.3 and Number Field extension.


[INSTALLATION]

1. Download the Number Field extension (http://github.com/pointybeard/numberfield/tree) and upload the 'numberfield' folder to your Symphony 'extensions' folder.

1. Upload the 'incrementnumberfield' folder in this archive to your Symphony 'extensions' folder.

2. Enable it by selecting the "Field: Increment Number", choose Enable from the with-selected menu, then click Apply.

3. You can now add the "Increment Number" field to your sections.


[CHANGES]

1.1 (2009-09-28)
- Only increment when field called through Frontend. Prevents increment when using Reflection field.
- Allow a With Selected action to reset field value to 0

1.0 (2008-12-19)
- Initial release