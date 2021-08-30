alias nba='nano ~/.bashrc'
alias omni='cd C:/Code/Omni'
alias awsl='aws-azure-login --no-prompt -m gui'

alias la='ls -la'
alias lt='ls -lt'

alias ..='cd ..'
alias .2='cd ../..'
alias .3='cd ../../..'
alias .4='cd ../../../..'
alias .5='cd ../../../../..'

alias mig='php artisan migrate:fresh --seed'
alias phpunit='vendor/bin/phpunit'

export XDEBUG_CONFIG="idekey=VSCODE"

alias gcs='git checkout staging'
alias gcm='git checkout master'
alias gp='git pull'
alias gba='git branch -a'
alias gs='git status'
alias smoke='php artisan dusk --group=smoke'

# call artisan easier
art() {
   php artisan "$@"
}
