import Repository from '../repositories/RepositoryFactory'
import {ErrorHandler} from '../Helpers/ErrorHander'

export class MiniGridService {
    constructor() {
        this.repository = Repository.get('minigrid')
        this.miniGrids = []
    }

    async getMiniGrids() {
        try {

            let response = await this.repository.list()

            if (response.status === 200 || response.status === 201) {
                this.miniGrids = response.data.data

                return this.miniGrids
            } else {
                return new ErrorHandler(response.error, 'http', response.status)
            }
        } catch (e) {
            return new ErrorHandler(e, 'http')
        }
    }

    async getMiniGridData(miniGridId) {
        try {

            let response = await this.repository.get(miniGridId)

            if (response.status === 200) {
                return response.data.data

            } else {
                return new ErrorHandler(response.error, 'http', response.status)
            }
        } catch (e) {
            return new ErrorHandler(e, 'http')
        }
    }

    async setMiniGridDataStream(miniGridId, data_stream) {
        try {
            let miniGrid_PM = {
                data_stream: data_stream
            }
            let response = await this.repository.watch(miniGridId, miniGrid_PM)

            if (response.status === 200) {
                return response.data.data

            } else {

                return new ErrorHandler(response.error, 'http', response.status)
            }
        } catch (e) {
            let errorMessage = e.response.data.data.message
            return new ErrorHandler(errorMessage, 'http')
        }
    }

    async getMiniGridDataStreams(data_stream) {
        try {

            let response = await this.repository.listDataStream(data_stream)

            if (response.status === 200) {
                this.miniGrids = response.data.data

                return this.miniGrids
            } else {
                return new ErrorHandler(response.error, 'http', response.status)
            }
        } catch (e) {
            let errorMessage = e.response.data.data.message
            return new ErrorHandler(errorMessage, 'http')
        }
    }

}
