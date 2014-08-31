set noexpandtab
set ts=4
highlight ExtraWhitespace ctermbg=darkgreen guibg=darkgreen
call matchadd('ExtraWhitespace', '\s\+$')
call matchadd('ExtraWhitespace', '^ \+')
autocmd ColorScheme * highlight ExtraWhitespace ctermbg=darkgreen guibg=darkgreen

"set listchars=tab:→ ,trail:⋅,extends:>,precedes:<
"set list
