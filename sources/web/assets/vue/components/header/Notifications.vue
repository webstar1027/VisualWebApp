<template>
	<div class="notifications-wrap">
		<div class="notification-icon" @click="active = !active">
			<el-badge v-if="notifications.length > 0" :value="notifications.length" class="item" type="primary">
				<i class="material-icons">notifications_active</i>
			</el-badge>
			<i v-else class="material-icons">notifications_active</i>
		</div>
		<div class="notification-list" :class="{'active':active}">
			<div class="notification-list-header d-flex">
				<span class="font-small font-weight-semibold">Notifications</span>
			</div>
			<div class="notification-items">
				<div class="notification-item d-flex" v-for="(n,i) in notifications" :key="i">
					<div class="font-smaller">
						<p>{{n.subject}}</p>
						<p>{{n.content}}</p>
					</div>
					<div v-if="n.type==='invitationEnsemble'" class="ml-auto d-flex">
						<el-button class="custom-notification-button btn-accept" @click="acceptInvitation(n.ensembleId, n.entiteId)">
							<md-icon>check</md-icon>
						</el-button>
						<el-button class="custom-notification-button btn-deny" @click="denyInvitation(n.ensembleId, n.entiteId)">
							<md-icon>close</md-icon>
						</el-button>
					</div>
					<div v-if="n.type==='invitationEntite'" class="ml-auto d-flex">
						<el-button class="custom-notification-button btn-accept" @click="acceptInvitationEntite(n.entiteId, n.utilisateurId)">
							<md-icon>check</md-icon>
						</el-button>
						<el-button class="custom-notification-button btn-deny" @click="denyInvitationEntite(n.entiteId, n.utilisateurId)">
							<md-icon>close</md-icon>
						</el-button>
					</div>
					<div v-if="n.type==='leaveEnsemble'" class="ml-auto d-flex">
						<el-button class="custom-notification-button btn-deny" @click="remove(n.ensembleId, n.entiteId)">
							<md-icon>close</md-icon>
						</el-button>
					</div>
					<div v-if="n.type==='REMOVE_REFERENT_ENTITE' || n.type==='SHARE_SIMULATION'" class="ml-auto d-flex">
						<el-button class="custom-notification-button btn-deny" @click="read(n.id)">
							<md-icon>close</md-icon>
						</el-button>
					</div>
				</div>
			</div>
		</div>
	</div>
</template>
<script>
export default {
	name: "Notifications",
	data() {
		return {
			active: false,
			notifications: []
		};
	},
	mounted() {
		this.getNotifications();
	},
	methods: {
		getNotifications() {
			this.notifications = [];
			this.$apollo
				.query({
					query: require("../../graphql/administration/notifications/fetchAllNotifications.gql"),
					fetchPolicy: 'no-cache'
				})
				.then(response => {
					if (response.data && response.data.fetchAllNotifications) {
						const notifications = JSON.parse(response.data.fetchAllNotifications);
						this.getGeneralNotifications(notifications.general);
						this.getNotificationEnsembles(notifications.inviteEnsemble, notifications.leaveEnsemble);
						this.getNotificationEntites(notifications.inviteEntite);
					}
				});
		},
		getGeneralNotifications(notifications) {
			notifications.forEach(notification => {
				this.notifications.push({
					id: notification.id,
					subject: "",
					content: notification.message,
					type: notification.type
				});
			})
		},
		getNotificationEnsembles(inviteEnsembles, leaveEnsembles) {
			inviteEnsembles.forEach(notification => {
				this.notifications.push({
					entiteId: notification.entite.id,
					ensembleId: notification.ensemble.id,
					subject: "Souhaitez vous rejoindre l'ensemble",
					content: notification.ensemble.nom,
					type: 'invitationEnsemble'
				});
			})
			leaveEnsembles.forEach(notification => {
				this.notifications.push({
					entiteId: notification.entite.id,
					ensembleId: notification.ensemble.id,
					subject: "The entite left the ensemble",
					content: notification.entite.nom,
					type: 'leaveEnsemble'
				});
			})
		},
		acceptInvitation(ensembleId, entiteId) {
			this.$apollo
				.mutate({
					mutation: require("../../graphql/administration/invitations/acceptInvitationEnsemble.gql"),
					variables: {
						ensembleID: ensembleId,
						entiteID: entiteId
					}
				})
				.then(() => {
					this.$router.go(0);
				})
				.catch(error => {
					this.$message({
						showClose: true,
						message: error.networkError.result,
						type: "error",
						duration: 10000
					});
				});
		},
		denyInvitation(ensembleId, entiteId) {
			this.$apollo
				.mutate({
					mutation: require("../../graphql/administration/invitations/denyInvitationEnsemble.gql"),
					variables: {
						ensembleID: ensembleId,
						entiteID: entiteId
					}
				})
				.then(() => {
					this.getNotifications();
				})
				.catch(error => {
					this.$message({
						showClose: true,
						message: error.networkError.result,
						type: "error",
						duration: 10000
					});
				});
		},
		remove(ensembleId, entiteId) {
			this.$apollo
				.mutate({
					mutation: require("../../graphql/administration/invitations/deleteInvitationEnsemble.gql"),
					variables: {
						ensembleID: ensembleId,
						entiteID: entiteId
					}
				})
				.then(() => {
					this.getNotifications();
				})
				.catch(error => {
					this.$message({
						showClose: true,
						message: error.networkError.result,
						type: "error",
						duration: 10000
					});
				});
		},
		getNotificationEntites(notifications) {
			notifications.forEach(notification => {
				this.notifications.push({
					entiteId: notification.entite.id,
					utilisateurId: notification.utilisateur.id,
					subject: "Souhaitez vous rejoindre l'entite",
					content: notification.entite.nom,
					type: 'invitationEntite'
				});
			})
		},
		acceptInvitationEntite(entiteId, utilisateurId) {
			this.$apollo
				.mutate({
					mutation: require("../../graphql/administration/invitations/acceptInvitationEntite.gql"),
					variables: {
						entiteID: entiteId,
						utilisateurID: utilisateurId
					}
				})
				.then(() => {
					this.$router.go(0);
				})
				.catch(error => {
					this.$message({
						showClose: true,
						message: error.networkError.result,
						type: "error",
						duration: 10000
					});
				});
		},
		denyInvitationEntite(entiteId, utilisateurId) {
			this.$apollo
				.mutate({
					mutation: require("../../graphql/administration/invitations/denyInvitationEntite.gql"),
					variables: {
						entiteID: entiteId,
						utilisateurID: utilisateurId
					}
				})
				.then(() => {
					this.getNotifications();
				})
				.catch(error => {
					this.$message({
						showClose: true,
						message: error.networkError.result,
						type: "error",
						duration: 10000
					});
				});
		},
		read(notificationId) {
			this.$apollo
				.mutate({
					mutation: require("../../graphql/administration/notifications/readNotification.gql"),
					variables: {
						notificationID: notificationId
					}
				})
				.then(() => {
					this.getNotifications();
				})
				.catch(error => {
					this.$message({
						showClose: true,
						message: error.networkError.result,
						type: "error",
						duration: 10000
					});
				});
		}
	}
};
</script>
