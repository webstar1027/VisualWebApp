<template>
    <el-dialog
            title="Historique du partage"
            :visible.sync="show"
            width="50%"
            class="share-dialog"
            v-if="simulationShareHistory !== null"
            :close-on-click-modal="false"
            :before-close="close"
    >
        <el-row>
            <ApolloQuery
                    :query="require('../../../../graphql/administration/partagers/fetchPartage.gql')"
                    :variables="{
							limit: limit,
							offset: offset,
							simulationID: simulationShareHistory.simulation.id
						}"
            >
                <template slot-scope="{ result:{ loading, error, data }, isLoading }">
                    <div v-if="error">Une erreur est survenue !</div>
                    <template>
                        <div v-if="simulationShareHistory.partager.userType !== 'shared'">
                <span class="filter-title">
                  Historique proriétaire /
                  <small>
                    Vous avez partagé cette simulation à
                    <strong
                            style="font-size: 18px;"
                    >{{ getCount(data, 'fetchPartage') }}</strong>
                    utilisateur(s)
                  </small>
                </span>
                        </div>
                        <div v-else>
                <span class="filter-title">
                  Historique de partage de la simulation /
                  <small>
                    Cette simulation a été partagée par
                    <strong
                            style="font-size: 18px;"
                    >{{ getCount(data, 'fetchPartage') }}</strong>
                    utilisateur(s)
                  </small>
                </span>
                        </div>
                    </template>
                    <el-table
                            v-loading="isLoading === 1"
                            ref="shareHistoryTable"
                            :data="getItems(data, 'fetchPartage')"
                    >
                        <el-table-column sortable="custom" type="index" width="80"></el-table-column>
                        <el-table-column
                                sortable="custom"
                                prop="partageType"
                                label="Type de Partage"
                                width="220"
                        ></el-table-column>
                        <el-table-column sortable="custom" prop="dateCreation" label="Date" width="180">
                            <template slot-scope="scope">
                                <span>{{ scope.row.dateCreation | dateFR }}</span>
                            </template>
                        </el-table-column>
                        <el-table-column sortable="custom" prop="entite.nom" label="Information">
                            <template slot-scope="scope">
                                <span>{{ messageBody(scope.row, simulationShareHistory.partager.userType) }} {{ scope.row.dateCreation | dateFR }}</span>
                            </template>
                        </el-table-column>
                    </el-table>
                    <el-pagination
                            v-if="isLoading === 0"
                            @current-change="handleCurrentChange"
                            style="text-align: center"
                            layout="prev, pager, next"
                            :current-page="currentPage"
                            :total="getCount(data, 'fetchPartage')"
                    ></el-pagination>
                </template>
            </ApolloQuery>
        </el-row>

        <el-row class="mt-5">
            <el-col :span="6" :offset="9">
                <el-button type="info" style="width: 200px" @click="close">Fermer</el-button>
            </el-col>
        </el-row>
    </el-dialog>
</template>
<script>
    import moment from "moment";
    import {getCount, getItems} from '../../../../utils/helpers';

    export default {
      name: "SimulationPartageHistorique",
      props: [
        'show',
        'limit',
        'offset',
        'simulationShareHistory',
        'currentPage',
        'close',
        'handleCurrentChange'
      ],
      methods: {
        getCount: getCount,
        getItems: getItems,
        messageBody(row, type) {
          if (type === "shared") {
            return `Partagée par ${row.ownerUtilisateur.email} chez ${row.owner.nom} en ${row.partageType} le`;
          } else {
            return `Partagée à ${row.utilisateur.email} chez ${row.entite.nom} en ${row.partageType} le`;
          }
        },
      },
      filters: {
        dateFR(value) {
          return value ? moment(String(value)).format("DD/MM/YYYY") : "";
        }
      }
    }

</script>