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
          sh 'if [ ! -d docker4php ] ; then git clone https://github.com/janetuk/docker4php.git ; fi  '
          sh 'if [ -d docker4php ] ; then cd docker4php ; mkdir -p data/web ; fi'
        }
      }
    }

    stage('webci') {
      steps {
        sh 'cd web ;if [ ! -d webci-myjisc ] ; then git clone https://github.com/neiljisc/webci-myjisc.git ; fi ; cd webci-myjisc && git checkout myjisc && git pull'
      }
    }

    stage('checkout') {
      steps {
        sh '(cd docker4php/data/web && rm -rf drupal && ln -s  ../../../ drupal)'
        sh 'cd docker4php ;git checkout drupal9_web ; git pull ;   source .env && make && echo export PROJECT_NAME=$PROJECT_NAME >  .pname '
      }
    }
    
    stage('composer') {
      steps {
        sh "cd docker4php ; source .pname && export CMD='composer install' &&  sh -c 'make fpmi '"
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
        sh 'cd docker4php ; source .pname ; docker cp /Users/neil.mckett/projects/db_dumps/myjisc-latest.sql ${PROJECT_NAME}_php:/tmp ; export CMD="mysql -uroot -ppassword -hmariadb drupal < /tmp/myjisc-latest.sql " ; make fpmi '
      }
    }

    stage ('run-updates') {
      steps {
        sh 'cd docker4php; export CMD="web/webci-myjisc/scripts/run-updates.sh" ; make fpmi'
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
