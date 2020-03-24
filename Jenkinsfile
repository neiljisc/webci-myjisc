pipeline {

  agent any 

  environment{
    REPO_EXISTS = fileExists 'data/web/drupal'
  }

//  environment {
//    IMAGE = 'registry.gitlab.com/XXXXX/bible-server'
//    DOCKER_REGISTRY_CREDENTIALS = credentials('DOCKER_REGISTRY_CREDENTIALS')
//  }

  options {
    timeout(10)
  }

  stages {
    stage('Init') {
      steps {
        sh 'mkdir -p data/web'
      }
    }

    stage('clone-docker4php') {
      steps {
        catchError(buildResult: 'SUCCESS', stageResult: 'FAILURE') {
          sh 'if [ ! -d docker4php ] ; then git clone https://github.com/janetuk/docker4php.git ; fi  '
        }
      }
    }

//    stage('create-drupal-directory') {
//      steps {
//        catchError(buildResult: 'SUCCESS', stageResult: 'FAILURE') { 
//          sh 'cd docker4php ; mkdir -p data/web ; cd data/web ; ln -s ../../../ drupal' 
//        }
//      }
//    }
 
   // stage('clone-myjisc') {
    //  steps {
     //   catchError(buildResult: 'SUCCESS', stageResult: 'FAILURE') {
      //    sh 'git clone https://github.com/janetuk/myjisc.git docker4php/data/web/drupal'
 //      }
  //    }
  //  }

    stage('webci') {
      steps {
        sh 'cd web ;if [ ! -d webci-myjisc ] ; then git clone https://github.com/neiljisc/webci-myjisc.git ; fi ; cd webci-myjisc && git checkout drupal-umami && git pull'
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
//        sh  "export CMD="\""echo "\'"drop database drupal ; create database drupal"\'" |  mysql -uroot -ppassword -hmariadb drupal"\"" ; make fpmi "
        sh  'cd docker4php ; export CMD="echo drop database if exists drupal |  mysql -uroot -ppassword -hmariadb" ; make fpmi '
        sh  'cd docker4php ; export CMD="echo create database drupal |  mysql -uroot -ppassword -hmariadb" ; make fpmi '

      }
    }

    stage('import-db') {
      steps {
        sh 'cd docker4php ; source .pname ; docker cp /Users/neil.mckett/projects/db_dumps/drupal-umami-latest.sql ${PROJECT_NAME}_php:/tmp ; CMD="mysql -uroot -ppassword -hmariadb drupal < /tmp/drupal-umami-latest.sql " ; make fpmi '
      }
    }

    stage ('run-updates') {
      steps {
        sh 'cd docker4php; export CMD="web/webci-myjisc/scripts/run-updates.sh" ; make fpmi'
      }
    }
 
//    stage('Test') {
//      steps {
//        sh 'yarn'
//        sh 'npm test'
//      }
//    }

//    stage('Build') {
//      when {
//        branch '*/master'
//      }
//      steps {
//        sh 'docker login -u ${DOCKER_REGISTRY_CREDENTIALS_USR} -p ${DOCKER_REGISTRY_CREDENTIALS_PSW} registry.gitlab.com'
//        sh 'docker build -t ${IMAGE}:${BRANCH_NAME} .'
//        sh 'docker push ${IMAGE}:${BRANCH_NAME}'
//      }
//    }

//    stage('Deploy') {
//      when {
//        branch '*/master'
//      }
//      steps {
//        echo 'Deploying ..'
//      }
//    }
  }

  post {
    success {
//      mail to: "XXXXX@gmail.com", subject:"SUCCESS: ${currentBuild.fullDisplayName}", body: "Yay, we passed."
      echo  'succedeed'
    }
    failure {
      echo 'failed'
//      mail to: "XXXXX@gmail.com", subject:"FAILURE: ${currentBuild.fullDisplayName}", body: "Boo, we failed."
    }
  }
}
