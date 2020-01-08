#!/bin/sh

set -xe

# VPC
aws cloudformation deploy \
--stack-name "$ENVIRONMENT_NAME-vpc-stack" \
--template-file "./vpc.yaml" \
--parameter-overrides "EnvironmentName=$ENVIRONMENT_NAME" \
--no-fail-on-empty-changeset

# ECS Cluster
aws cloudformation deploy \
--stack-name "$ENVIRONMENT_NAME-ecs-cluster-stack" \
--template-file "./ecs-cluster.yaml" \
--parameter-overrides "EnvironmentName=$ENVIRONMENT_NAME" \
"VPCStackName=$ENVIRONMENT_NAME-vpc-stack" \
--capabilities CAPABILITY_IAM CAPABILITY_NAMED_IAM \
--no-fail-on-empty-changeset

# WAF
aws cloudformation deploy \
--stack-name "$ENVIRONMENT_NAME-waf-stack" \
--template-file "./waf.yaml" \
--parameter-overrides "ECSClusterStackName=$ENVIRONMENT_NAME-ecs-cluster-stack" \
--no-fail-on-empty-changeset

# Bastion
aws cloudformation deploy \
--stack-name "$ENVIRONMENT_NAME-bastion-stack" \
--template-file "./bastion.yaml" \
--parameter-overrides "EnvironmentName=$ENVIRONMENT_NAME" \
"VPCStackName=$ENVIRONMENT_NAME-vpc-stack" \
"KeyName=$ENVIRONMENT_NAME-bastion" \
"InstanceSGAllowSSHCIDR=$ALLOWED_IP_FOR_ACCESSING_DATABASE" \
--no-fail-on-empty-changeset

# RDS Aurora
aws cloudformation deploy \
--stack-name "$ENVIRONMENT_NAME-rds-aurora-mysql-stack" \
--template-file "./rds-aurora-mysql.yaml" \
--parameter-overrides "EnvironmentName=$ENVIRONMENT_NAME" \
"VPCStackName=$ENVIRONMENT_NAME-vpc-stack" \
"ECSClusterStackName=$ENVIRONMENT_NAME-ecs-cluster-stack" \
"BastionStackName=$ENVIRONMENT_NAME-bastion-stack" \
"DatabaseName=$DATABASE_NAME" \
"DatabaseUser=$DATABASE_USER" \
"DatabasePassword=$DATABASE_PASSWORD" \
--capabilities CAPABILITY_IAM CAPABILITY_NAMED_IAM \
--no-fail-on-empty-changeset

# ECS Service
aws cloudformation deploy \
--stack-name "$ENVIRONMENT_NAME-ecs-service-stack" \
--template-file "./ecs-service.yaml" \
--parameter-overrides "VPCStackName=$ENVIRONMENT_NAME-vpc-stack" \
"ECSClusterStackName=$ENVIRONMENT_NAME-ecs-cluster-stack" \
"AuroraStackName=$ENVIRONMENT_NAME-rds-aurora-mysql-stack" \
"DockerImage=$AWS_ACCOUNT_ID.dkr.ecr.$AWS_DEFAULT_REGION.amazonaws.com/$AWS_REGISTRY_NAME:$CI_COMMIT_TAG" \
"ContainerCpu=$CONTAINER_CPU" \
"ContainerMemory=$CONTAINER_MEMORY" \
"DatabaseName=$DATABASE_NAME" \
"DatabaseUser=$DATABASE_USER" \
"DatabasePassword=$DATABASE_PASSWORD" \
"EnvPHPIniMemoryLimit=$PHP_INI_MEMORY_LIMIT" \
"EnvMailSender=$MAIL_SENDER" \
"EnvMailerURL=$MAILER_URL" \
--capabilities CAPABILITY_IAM CAPABILITY_NAMED_IAM \
--no-fail-on-empty-changeset

