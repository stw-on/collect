export const STATUS_DRAFT = 'draft'
export const STATUS_UPLOADING = 'uploading'
export const STATUS_UPLOADED = 'uploaded'
export const STATUS_ERROR = 'error'

export class UploadingFile {
  constructor(file) {
    this.id = null // TransferredFile ID
    this.file = file
    this.status = STATUS_DRAFT
    this.progress = 0
    this.error = null
  }

  get isErrorStatus() {
    return this.status === STATUS_ERROR
  }
}
