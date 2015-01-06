set noexpandtab
set ts=4
highlight ExtraWhitespace ctermbg=darkgreen guibg=darkgreen
call matchadd('ExtraWhitespace', '\s\+$')
call matchadd('ExtraWhitespace', '^ \+')

"set listchars=tab:→ ,trail:⋅,extends:>,precedes:<
"set list
