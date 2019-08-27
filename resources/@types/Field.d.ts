import { Child } from './Child'

export class Field {
  children: Child[]
  indexKey: string
  wrapLeft: string
  wrapRight: string
  value: any
  name: string
  min: number
  max: number
  resourceId: string | number
  resourceName: string
  viaRelationship: string
  viaResource: string
  viaResourceId: string | number
}
