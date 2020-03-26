pipeline {

  agent any 

  options {
    timeout(10)
  }

  stages {
    stage('Init') {
      steps {
        configFileProvider([configFile(fileId: '06662201-85d1-4d63-a6a9-c9daf3a8fc6f', variable: 'UMAMI_SETTINGS')]) {
          sh 'cd ./web/sites/default && cp $UMAMI_SETTINGS settings.php && ls' 
        }
      }
    }

    stage('clone-docker4php') {
      steps {
        catchError(buildResult: 'SUCCESS', stageResult: 'FAILURE') {
            dir ('docker4php') {
              git branch: 'drupal9_web', credentialsId: 'a3beddd6-e0fb-4e55-8928-6866e922b32e', url: 'https://github.com/janetuk/docker4php'
            }
        }
      }
    }

    stage('webci') {
      steps {
        catchError(buildResult: 'SUCCESS', stageResult: 'FAILURE') {
            dir ('web/webci-myjisc') {
              git branch: 'myjisc', credentialsId: 'a3beddd6-e0fb-4e55-8928-6866e922b32e', url: 'https://github.com/neiljisc/webci-myjisc'
            }
        }
        //sh 'cd web ;if [ ! -d webci-myjisc ] ; then git clone https://github.com/neiljisc/webci-myjisc.git ; fi ; cd webci-myjisc && git checkout myjisc && git pull'
      }
    }

    stage('cp jisc_ci module') {
      steps {
        sh 'cd web ; if [ ! -d modules/custom/jisc_ci ] ; then cp -rfp webci-myjisc/modules/custom/jisc_ci modules/custom ; fi '
      }
    }
    stage('checkout') {
      steps {
        sh '(cd docker4php && mkdir -p data/web && cd data/web && rm -rf drupal && ln -s  ../../../ drupal)'
        sh "cd docker4php ;git checkout drupal9_web ; echo \$PWD ;   source \$PWD/.env && id && groups && make && echo export PROJECT_NAME=\$PROJECT_NAME >  \$PWD/.pname "
      }
    }
    
    stage('composer') {
      steps {
        sh "cd docker4php ; source \$PWD/.pname && export CMD='cd / ; sudo rm -rf /var/www/html ; sudo ln -s /opt/var/www/html /var/www/html  ; cd /var/www/html ;rm -rf web/core ;  sudo chmod -R 777 modules /opt/var/www/html/sites ; composer install' &&  sh -c 'make fpmi '"
      }
    }

    stage('wipe-db') {
      steps {
        sh  'cd docker4php ; export CMD="echo drop database if exists drupal |  mysql -uroot -ppassword -hmariadb" ; make fpmi '
        sh  'cd docker4php ; export CMD="echo create database drupal |  mysql -uroot -ppassword -hmariadb" ; make fpmi '
      }
    }

    stage('import-db') {
      steps {
        sh 'cd docker4php ; source $PWD/.pname ; docker cp /opt/db_dumps/myjisc-latest.sql ${PROJECT_NAME}_php:/tmp ; export CMD="mysql -uroot -ppassword -hmariadb drupal < /tmp/myjisc-latest.sql " ; make fpmi '
      }
    }

    stage('install jisc_ci module') {
      steps {
        sh 'cd docker4php ;  export CMD="drush pm:enable jisc_ci" ; make fpmi'
      }
    }

    stage ('run-updates') {
      steps {
        sh 'cd docker4php; export CMD="web/webci-myjisc/scripts/run-updates.sh updates" ; make fpmi'
      }
    }

    stage('yarn') {
      steps {
        sh ' cd docker4php; export CMD="apk add yarn ; apk add npm ; sudo chmod -R 777 /opt/var/www/html/web/sites ; cd /opt/var/www/html/web/themes/jiscux ; npm config set user 0; npm config set unsafe-perm true ; yarn add @jisc/front-end-foundations ;  yarn install ; rm -rf front-end-foundations ; ln -s node_modules/@jisc/front-end-foundations  ; touch front-end-foundations/src/scss/1-settings/settings.variables.images ; npm install -g gulp ; gulp build" ; make nginxi '
      }
    }
 
  }

  post {
    success {
//      mail to: "XXXXX@gmail.com", subject:"SUCCESS: ${currentBuild.fullDisplayName}", body: "Yay, we passed."
      echo  'succeeded'
    }
    failure {
      echo 'failed'
//      mail to: "XXXXX@gmail.com", subject:"FAILURE: ${currentBuild.fullDisplayName}", body: "Boo, we failed."
    }
  }
}
