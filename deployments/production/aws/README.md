# Setup infrastructure on AWS using CloudFormation

There are two AWS accounts, one per environment (`staging` and `production`).

For each account, we use the same AWS CloudFormation templates:
* Each template create its own AWS CloudFormation **stack**, which will create/update resources on the AWS account.
* Most of them are used in GitLab CI but some are used directly from the CLI (one shot).

## Prerequisites

Make sure you have an account with full access to the AWS console and with programmatically access.
In other words, an account with an `Access key ID` and a `Secret access key`.

We'll use the image `mikesir87/aws-cli:latest` for the AWS CLI.

## First steps: one shot AWS CloudFormation templates

**Important:** the next commands will have to be run on each AWS account.

### Create a group with policies for GitLab CI

The template `gitlabci-group-policies.yaml` will create an IAM group with policies allowing a user to run the AWS 
CloudFormation templates from the GitLab CI jobs.

| Parameter   | Type   | Description        | Default Value          |
|-------------|--------|--------------------|------------------------|
| GroupName  | String | Name of the group  | GitLabCI  |
| PoliciesBaseName | String | Base name of the policies | GitLabCIPolicy |

From this folder, run the following command:

```bash
docker run --rm -it -v $(pwd):/aws -e "AWS_ACCOUNT_ID=the_aws_account_id" -e "AWS_DEFAULT_REGION=eu-central-1" -e "AWS_ACCESS_KEY_ID=the_aws_access_key_id" -e "AWS_SECRET_ACCESS_KEY=the_aws_secret_access_key" mikesir87/aws-cli:latest aws cloudformation create-stack --stack-name gitlabci-group-policies-stack --template-body file://gitlabci-group-policies.yaml --capabilities CAPABILITY_IAM CAPABILITY_NAMED_IAM
```

You may follow the progress of the stack `gitlabci-group-policies-stack` created by this template from the [AWS console](https://eu-central-1.console.aws.amazon.com/cloudformation/home?region=eu-central-1).

Once it has the status `CREATE_COMPLETE`, use the AWS console to create a user `gitlabci` using the IAM group created by the stack (`GitLabCI`).

**Important:** save the `Access key ID` and `Secret access key`. You will need them in order to populate the related GitLab CI environment variables.

### Create the Docker registry (AWS ECR)

The template `ecr-registry.yaml` will create the Docker registry (AWS ECR) where will be hosted the Docker image of
our application.

| Parameter       | Type   | Description            | Default Value |
|-----------------|--------|------------------------|---------------|
| RegistryName  | String | Name of the registry | visial-web |
| NumberOfImagesToKeep  | Number | Number of images to keep | 10 |  

From this folder, run the following command:

```bash
docker run --rm -it -v $(pwd):/aws -e "AWS_ACCOUNT_ID=the_aws_account_id" -e "AWS_DEFAULT_REGION=eu-central-1" -e "AWS_ACCESS_KEY_ID=the_aws_access_key_id" -e "AWS_SECRET_ACCESS_KEY=the_aws_secret_access_key" mikesir87/aws-cli:latest aws cloudformation create-stack --stack-name ecr-registry-stack --template-body file://ecr-registry.yaml
```

You may follow the progress of the stack `ecr-registry-stack` created by this template from the [AWS console](https://eu-central-1.console.aws.amazon.com/cloudformation/home?region=eu-central-1).

**Important:** save the `RegistryName` as you'll need it in order to populate the corresponding GitLab CI environment variable.

### SMTP

// TODO

### Preparing bastions

For each environment, we will have to create a key pair for the bastion. 

Go to the [AWS console](https://eu-central-1.console.aws.amazon.com/ec2/) and click on `Key Pairs` (under `NETWORK & SECURITY`).

Use the following names:

* `staging-bastion`
* `production-bastion`

Save the `.pem` files on the LoginMachine.

## Configure the GitLab CI jobs

The process is quite simple. Whenever a tag is created on GitLab, it will create a pipeline with:
* one job for building the Docker image of the application and pushing it to the `staging` AWS account Docker registry (automatic).
* one job for building the Docker image of the application and pushing it to the `production` AWS account Docker registry (automatic).
* one job for deploying to the `staging` AWS account (manual).
* one job for deploying to the `production` AWS account (manual).

While the two first jobs will run in parallel, the last two jobs will run one after the other.
In other words, you must deploy to the `staging` environment before deploying to the `production` environment.

### Building and pushing the Docker image

The jobs `build_push_docker_image_staging` and `build_push_docker_image_production` are based on 
a job "template" called `.build_push_docker_image_aws`.

This template requires the following environment variables:
* `AWS_ACCOUNT_ID`: the AWS account ID.
* `AWS_DEFAULT_REGION`: the default region (by default Frankfurt, e.g. `eu-central-1`)
* `AWS_ACCESS_KEY_ID`: the access key ID of the user `gitlabci`.
* `AWS_SECRET_ACCESS_KEY`: the secret access key of the user `gitlabci`.
* `AWS_REGISTRY_NAME`: the registry name.

In each "implementation" of this job template, those environment variables are overridden by environment variables created 
in `TCM Projects > Visial Web > CI / CD Settings > Variables` in our GitLab.

For instance, the `staging` environment use the following environment variables:
* `STAGING_AWS_ACCOUNT_ID`.
* `STAGING_AWS_DEFAULT_REGION`.
* `STAGING_AWS_ACCESS_KEY_ID`.
* `STAGING_AWS_SECRET_ACCESS_KEY`.
* `STAGING_AWS_REGISTRY_NAME`.

For the `production` environment, its the same but prefixed with `PRODUCTION_`.

### Deploying

The jobs `deploy_staging` and `deploy_production` are based on a job "template" called `.deploy_aws`.

Like the jobs `build_push_docker_image_staging` and `build_push_docker_image_production`, they use environment variables.

Those jobs will create the following stacks:
* `ENVIRONMENT-vpc-stack`: deploy the virtual network on AWS.
* `ENVIRONMENT-ecs-cluster-stack`: deploy the AWS Fargate cluster.
* `ENVIRONMENT-waf-stack`: deploy the protections against common attacks.
* `ENVIRONMENT-bastion-stack`: deploy a bastion in order to connect the the database remotely.
* `ENVIRONMENT-rds-aurora-mysql-stack`: deploy the database.
* `ENVIRONMENT-ecs-service-stack`: deploy the container previously built int the AWS Fargate cluster.

Take a look at the file `deploy_aws.sh` for more details.

#### How to

> bind a domain name with the application on AWS?

Once the stack `ENVIRONMENT-ecs-cluster-stack` is deployed, go to the [AWS console](https://eu-central-1.console.aws.amazon.com/ec2/home?region=eu-central-1)
and retrieve the A record of the load balancer. Send it to the client and ask it to bind it to a domain name.

**Important:** if you destroy the load balancer (for instance by deleting the `ENVIRONMENT-ecs-cluster-stack`), you'll have
to do this manipulation again.

> add HTTPS?

TODO

> connect to the database remotely?

The stack `ENVIRONMENT-bastion-stack` creates an EC2 instance on which you may connect via SSH.

This instance is the only way to connect to the database remotely.

**Important:** you may have to install a MySQL client in this instance. If this instance is destroyed, a new instance will
automatically be created and you'll have to reinstall everything in it.

