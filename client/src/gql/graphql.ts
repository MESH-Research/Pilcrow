/* eslint-disable */
import type { TypedDocumentNode as DocumentNode } from '@graphql-typed-document-node/core';
export type Maybe<T> = T | null;
export type InputMaybe<T> = Maybe<T>;
export type Exact<T extends { [key: string]: unknown }> = { [K in keyof T]: T[K] };
export type MakeOptional<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]?: Maybe<T[SubKey]> };
export type MakeMaybe<T, K extends keyof T> = Omit<T, K> & { [SubKey in K]: Maybe<T[SubKey]> };
export type MakeEmpty<T extends { [key: string]: unknown }, K extends keyof T> = { [_ in K]?: never };
export type Incremental<T> = T | { [P in keyof T]?: P extends ' $fragmentName' | '__typename' ? T[P] : never };
/** All built-in and custom scalars, mapped to their actual values */
export type Scalars = {
  ID: { input: string; output: string; }
  String: { input: string; output: string; }
  Boolean: { input: boolean; output: boolean; }
  Int: { input: number; output: number; }
  Float: { input: number; output: number; }
  /** A date string with format `Y-m-d`, e.g. `2011-05-23`. */
  Date: { input: any; output: any; }
  /** A datetime string with format `Y-m-d H:i:s`, e.g. `2018-05-23 13:43:32`. */
  DateTime: { input: any; output: any; }
  DateTimeUtc: { input: any; output: any; }
  /** JSON-format data */
  JSON: { input: any; output: any; }
  /** Can be used as an argument to upload files using https://github.com/jaydenseric/graphql-multipart-request-spec */
  Upload: { input: any; output: any; }
};

/** Academic profile data for the profile metadata of a user */
export type AcademicProfiles = {
  __typename?: 'AcademicProfiles';
  humanities_commons?: Maybe<Scalars['String']['output']>;
  orcid_id?: Maybe<Scalars['String']['output']>;
};

export type AcceptSubmissionInviteInput = {
  id: Scalars['ID']['input'];
  name?: InputMaybe<Scalars['String']['input']>;
  password: Scalars['String']['input'];
  username: Scalars['String']['input'];
};

export type Audit = {
  __typename?: 'Audit';
  created_at: Scalars['DateTimeUtc']['output'];
  event?: Maybe<Scalars['String']['output']>;
  id?: Maybe<Scalars['ID']['output']>;
  new_values?: Maybe<Scalars['JSON']['output']>;
  old_values?: Maybe<Scalars['JSON']['output']>;
  updated_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  user: User;
};

export type Comment = {
  content: Scalars['String']['output'];
  created_at: Scalars['DateTimeUtc']['output'];
  created_by: User;
  deleted_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  id: Scalars['ID']['output'];
  read_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  updated_at: Scalars['DateTimeUtc']['output'];
  updated_by: User;
};

export type CreateInlineCommentInput = {
  content: Scalars['String']['input'];
  from?: InputMaybe<Scalars['Int']['input']>;
  parent_id?: InputMaybe<Scalars['ID']['input']>;
  reply_to_id?: InputMaybe<Scalars['ID']['input']>;
  style_criteria?: InputMaybe<Array<Scalars['ID']['input']>>;
  to?: InputMaybe<Scalars['Int']['input']>;
};

export type CreateMetaFormInput = {
  caption?: InputMaybe<Scalars['String']['input']>;
  name: Scalars['String']['input'];
  publication_id: Scalars['ID']['input'];
  required: Scalars['Boolean']['input'];
};

export type CreateMetaPromptInput = {
  caption?: InputMaybe<Scalars['String']['input']>;
  label: Scalars['String']['input'];
  meta_form_id: Scalars['ID']['input'];
  options?: InputMaybe<Scalars['JSON']['input']>;
  required?: InputMaybe<Scalars['Boolean']['input']>;
  type: MetaPromptType;
};

export type CreateOverallCommentInput = {
  content: Scalars['String']['input'];
  parent_id?: InputMaybe<Scalars['ID']['input']>;
  reply_to_id?: InputMaybe<Scalars['ID']['input']>;
};

export type CreatePublicationInput = {
  home_page_content?: InputMaybe<Scalars['String']['input']>;
  is_publicly_visible?: InputMaybe<Scalars['Boolean']['input']>;
  name?: InputMaybe<Scalars['String']['input']>;
  new_submission_content?: InputMaybe<Scalars['String']['input']>;
  style_criterias?: InputMaybe<CreateStyleCriteriaHasMany>;
};

export type CreateStyleCriteriaHasMany = {
  create?: InputMaybe<Array<CreateStyleCriteriaInput>>;
};

export type CreateStyleCriteriaInput = {
  description?: InputMaybe<Scalars['String']['input']>;
  icon?: InputMaybe<Scalars['String']['input']>;
  name: Scalars['String']['input'];
};

/** Input type for creating a new submission via the createSubmissionDraft mutation */
export type CreateSubmissionDraftInput = {
  publication_id: Scalars['ID']['input'];
  submitters?: InputMaybe<CreateSubmissionUserInput>;
  title: Scalars['String']['input'];
};

/** Input type for creating a new association between an uploaded file and a submission */
export type CreateSubmissionFileInput = {
  file_upload: Scalars['Upload']['input'];
  submission_id: Scalars['ID']['input'];
};

/** Input type for connecting newly created submissions to files via nested mutation */
export type CreateSubmissionFilesHasMany = {
  create: Scalars['Upload']['input'];
};

export type CreateSubmissionUserInput = {
  connect: Array<Scalars['ID']['input']>;
};

/** Input type for creating a new user via the userCreate mutation */
export type CreateUserInput = {
  /** Email address. Must be unique. (required) */
  email: Scalars['String']['input'];
  /** Display name. (optional) */
  name?: InputMaybe<Scalars['String']['input']>;
  /** Password used to login to the application. (required) */
  password: Scalars['String']['input'];
  /** Username. Must be unique. (required) */
  username: Scalars['String']['input'];
};

export type DeleteCommentInput = {
  comment_id: Scalars['ID']['input'];
  submission_id: Scalars['ID']['input'];
};

export type GeneralSettings = {
  __typename?: 'GeneralSettings';
  site_name?: Maybe<Scalars['String']['output']>;
};

export type GeneralSettingsInput = {
  site_name?: InputMaybe<Scalars['String']['input']>;
};

/** A button that apepars on the Login page for a supported external identity provider */
export type IdentityProvider = {
  __typename?: 'IdentityProvider';
  icon: Scalars['String']['output'];
  label: Scalars['String']['output'];
  login_url: Scalars['String']['output'];
  name: Scalars['String']['output'];
};

/** An inline comment of a submission */
export type InlineComment = Comment & {
  __typename?: 'InlineComment';
  content: Scalars['String']['output'];
  created_at: Scalars['DateTimeUtc']['output'];
  created_by: User;
  deleted_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  from?: Maybe<Scalars['Int']['output']>;
  id: Scalars['ID']['output'];
  read_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  replies?: Maybe<Array<InlineCommentReply>>;
  style_criteria?: Maybe<Array<InlineCommentStyleCriteria>>;
  to?: Maybe<Scalars['Int']['output']>;
  updated_at: Scalars['DateTimeUtc']['output'];
  updated_by: User;
};


/** An inline comment of a submission */
export type InlineCommentRepliesArgs = {
  trashed?: InputMaybe<Trashed>;
};

export type InlineCommentHasManyInput = {
  create?: InputMaybe<Array<CreateInlineCommentInput>>;
  update?: InputMaybe<Array<UpdateInlineCommentInput>>;
};

/** A reply to an inline comment of a submission */
export type InlineCommentReply = Comment & {
  __typename?: 'InlineCommentReply';
  content: Scalars['String']['output'];
  created_at: Scalars['DateTimeUtc']['output'];
  created_by: User;
  deleted_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  id: Scalars['ID']['output'];
  parent_id: Scalars['ID']['output'];
  read_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  reply_to_id: Scalars['ID']['output'];
  updated_at: Scalars['DateTimeUtc']['output'];
  updated_by: User;
};

/** The static style criteria of an inline comment */
export type InlineCommentStyleCriteria = {
  __typename?: 'InlineCommentStyleCriteria';
  icon?: Maybe<Scalars['String']['output']>;
  id?: Maybe<Scalars['ID']['output']>;
  name?: Maybe<Scalars['String']['output']>;
};

/** Key/Value pairs input */
export type KeyValueInput = {
  key?: InputMaybe<Scalars['String']['input']>;
  value?: InputMaybe<Scalars['String']['input']>;
};

/** A response from an external identity provider */
export type LoginOauthResponse = {
  __typename?: 'LoginOauthResponse';
  action?: Maybe<Scalars['String']['output']>;
  provider?: Maybe<Provider>;
  user?: Maybe<ProviderUser>;
};

export type MarkInlineCommentsReadInput = {
  comment_ids: Array<Scalars['ID']['input']>;
  submission_id: Scalars['ID']['input'];
};

export type MarkOverallCommentsReadInput = {
  comment_ids: Array<Scalars['ID']['input']>;
  submission_id: Scalars['ID']['input'];
};

export type MetaForm = {
  __typename?: 'MetaForm';
  caption?: Maybe<Scalars['String']['output']>;
  id: Scalars['ID']['output'];
  meta_prompts?: Maybe<Array<MetaPrompt>>;
  name: Scalars['String']['output'];
  order: Scalars['Int']['output'];
  publication?: Maybe<Publication>;
  required: Scalars['Boolean']['output'];
};

export type MetaPrompt = {
  __typename?: 'MetaPrompt';
  caption?: Maybe<Scalars['String']['output']>;
  id: Scalars['ID']['output'];
  label: Scalars['String']['output'];
  options?: Maybe<Scalars['JSON']['output']>;
  order: Scalars['Int']['output'];
  required: Scalars['Boolean']['output'];
  type: MetaPromptType;
};

export enum MetaPromptType {
  Checkbox = 'CHECKBOX',
  Input = 'INPUT',
  Select = 'SELECT',
  Textarea = 'TEXTAREA'
}

export type Mutation = {
  __typename?: 'Mutation';
  /** Accept an invitation to a submission as the assigned role */
  acceptSubmissionInvite?: Maybe<User>;
  /** Run an artisan command and get the output as a string. */
  artisanCommand: Scalars['String']['output'];
  /**
   * Use a model factory to create a new database object.
   * Returns Models created
   *
   * (DOES create entries in the DB.)
   */
  createFactory?: Maybe<Array<User>>;
  /** Create a new publication */
  createPublication: Publication;
  /** Create a new submission under a publication */
  createSubmissionDraft: Submission;
  /** Create a new user */
  createUser: User;
  /** Delete an inline comment of a submission */
  deleteInlineComment: Submission;
  /** Delete an overall comment of a submission */
  deleteOverallComment: Submission;
  /**
   * Login a user using only an email
   *
   * Return the user model of the logged in user.
   */
  forceLogin?: Maybe<User>;
  /**
   * Create a staged user, assign them as a review coordinator of a submisison,
   * and send them an email notification inviting them to accept the assignment
   */
  inviteReviewCoordinator?: Maybe<Submission>;
  /**
   * Create a staged user, assign them as a reviewer of a submisison,
   * and send them an email notification inviting them to accept the assignment"
   */
  inviteReviewer?: Maybe<Submission>;
  /** Log in to a new session and get the user. */
  login: User;
  loginOauthCallback?: Maybe<LoginOauthResponse>;
  /** Log out from the current session, showing the user one last time. */
  logout?: Maybe<User>;
  /**
   * Use a model factory to get data for a model object.
   * Returns Models created.
   *
   * (Does not create entries in the DB)."
   */
  makeFactory?: Maybe<Array<User>>;
  /** Mark all of a user's notification as read */
  markAllNotificationsRead?: Maybe<Scalars['Int']['output']>;
  markInlineCommentRepliesRead: Array<Maybe<InlineCommentReply>>;
  markInlineCommentsRead: Array<Maybe<InlineComment>>;
  /** Mark a user notification as read */
  markNotificationRead: Notification;
  markOverallCommentRepliesRead: Array<Maybe<OverallCommentReply>>;
  markOverallCommentsRead: Array<Maybe<OverallComment>>;
  metaFormCreate: MetaForm;
  metaFormDelete: MetaForm;
  metaFormUpdate: Array<MetaForm>;
  metaPromptCreate: MetaPrompt;
  metaPromptDelete: MetaPrompt;
  metaPromptUpdate: Array<MetaPrompt>;
  registerOauthUser?: Maybe<User>;
  /** Resend an email notification inviting a staged review coordinator to accept the assignment */
  reinviteReviewCoordinator?: Maybe<Submission>;
  /** Resend an email notification inviting a staged reviewer to accept the assignment */
  reinviteReviewer?: Maybe<Submission>;
  /** Send a password reset email to a specified email address */
  requestPasswordReset?: Maybe<Scalars['Boolean']['output']>;
  /** Update a password from a password reset request */
  resetPassword?: Maybe<User>;
  saveGeneralSettings?: Maybe<GeneralSettings>;
  /** (Re)send a verification email to a user.   */
  sendEmailVerification: User;
  submissionMetaFormUpdate?: Maybe<SubmissionMetaResponse>;
  updatePublication?: Maybe<Publication>;
  /** Update an existing submission */
  updateSubmission: Submission;
  /** Update the content of a submission */
  updateSubmissionContent: Submission;
  /** Update the content of a submission with a file upload */
  updateSubmissionContentWithFile: Submission;
  /** Update user information */
  updateUser: User;
  /** Verify the currently logged in user's email address */
  verifyEmail: User;
  /** Verify access to an invitation to a submission as the assigned role */
  verifySubmissionInvite?: Maybe<User>;
};


export type MutationAcceptSubmissionInviteArgs = {
  expires: Scalars['String']['input'];
  token: Scalars['String']['input'];
  user?: InputMaybe<AcceptSubmissionInviteInput>;
  uuid: Scalars['String']['input'];
};


export type MutationArtisanCommandArgs = {
  command: Scalars['String']['input'];
  parameters?: InputMaybe<Array<KeyValueInput>>;
};


export type MutationCreateFactoryArgs = {
  attributes?: InputMaybe<Array<KeyValueInput>>;
  model: Scalars['String']['input'];
  times?: InputMaybe<Scalars['Int']['input']>;
};


export type MutationCreatePublicationArgs = {
  publication: CreatePublicationInput;
};


export type MutationCreateSubmissionDraftArgs = {
  input: CreateSubmissionDraftInput;
};


export type MutationCreateUserArgs = {
  user: CreateUserInput;
};


export type MutationDeleteInlineCommentArgs = {
  input: DeleteCommentInput;
};


export type MutationDeleteOverallCommentArgs = {
  input: DeleteCommentInput;
};


export type MutationForceLoginArgs = {
  email: Scalars['String']['input'];
};


export type MutationInviteReviewCoordinatorArgs = {
  input?: InputMaybe<InviteSubmissionUserInput>;
};


export type MutationInviteReviewerArgs = {
  input?: InputMaybe<InviteSubmissionUserInput>;
};


export type MutationLoginArgs = {
  email: Scalars['String']['input'];
  password: Scalars['String']['input'];
};


export type MutationLoginOauthCallbackArgs = {
  code: Scalars['String']['input'];
  provider_name: Scalars['String']['input'];
};


export type MutationMakeFactoryArgs = {
  attributes?: InputMaybe<Array<KeyValueInput>>;
  model: Scalars['String']['input'];
  times?: InputMaybe<Scalars['Int']['input']>;
};


export type MutationMarkInlineCommentRepliesReadArgs = {
  input: MarkInlineCommentsReadInput;
};


export type MutationMarkInlineCommentsReadArgs = {
  input: MarkInlineCommentsReadInput;
};


export type MutationMarkNotificationReadArgs = {
  id: Scalars['ID']['input'];
};


export type MutationMarkOverallCommentRepliesReadArgs = {
  input: MarkOverallCommentsReadInput;
};


export type MutationMarkOverallCommentsReadArgs = {
  input: MarkOverallCommentsReadInput;
};


export type MutationMetaFormCreateArgs = {
  input: CreateMetaFormInput;
};


export type MutationMetaFormDeleteArgs = {
  id: Scalars['ID']['input'];
};


export type MutationMetaFormUpdateArgs = {
  input: Array<UpdateMetaFormInput>;
};


export type MutationMetaPromptCreateArgs = {
  input: CreateMetaPromptInput;
};


export type MutationMetaPromptDeleteArgs = {
  id: Scalars['ID']['input'];
};


export type MutationMetaPromptUpdateArgs = {
  input: Array<UpdateMetaPromptInput>;
};


export type MutationRegisterOauthUserArgs = {
  input?: InputMaybe<RegisterOauthUserInput>;
};


export type MutationReinviteReviewCoordinatorArgs = {
  input?: InputMaybe<ReinviteSubmissionUserInput>;
};


export type MutationReinviteReviewerArgs = {
  input?: InputMaybe<ReinviteSubmissionUserInput>;
};


export type MutationRequestPasswordResetArgs = {
  email: Scalars['String']['input'];
};


export type MutationResetPasswordArgs = {
  input?: InputMaybe<ResetPasswordInput>;
};


export type MutationSaveGeneralSettingsArgs = {
  settings: GeneralSettingsInput;
};


export type MutationSendEmailVerificationArgs = {
  id?: InputMaybe<Scalars['ID']['input']>;
};


export type MutationSubmissionMetaFormUpdateArgs = {
  input?: InputMaybe<SubmissionMetaFormUpdate>;
};


export type MutationUpdatePublicationArgs = {
  publication: UpdatePublicationInput;
};


export type MutationUpdateSubmissionArgs = {
  input: UpdateSubmissionInput;
};


export type MutationUpdateSubmissionContentArgs = {
  input: UpdateSubmissionContent;
};


export type MutationUpdateSubmissionContentWithFileArgs = {
  input: CreateSubmissionFileInput;
};


export type MutationUpdateUserArgs = {
  user: UpdateUserInput;
};


export type MutationVerifyEmailArgs = {
  expires: Scalars['String']['input'];
  token: Scalars['String']['input'];
};


export type MutationVerifySubmissionInviteArgs = {
  expires: Scalars['String']['input'];
  token: Scalars['String']['input'];
  uuid: Scalars['String']['input'];
};

export type Notifiable = Notification | User;

/** A notification for an event */
export type Notification = {
  __typename?: 'Notification';
  created_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  data?: Maybe<NotificationData>;
  id: Scalars['ID']['output'];
  notifiable: Notifiable;
  read_at?: Maybe<Scalars['DateTime']['output']>;
};

/** JSON data for a notification */
export type NotificationData = {
  __typename?: 'NotificationData';
  action?: Maybe<Scalars['String']['output']>;
  body?: Maybe<Scalars['String']['output']>;
  invitee?: Maybe<User>;
  inviter?: Maybe<User>;
  publication?: Maybe<Publication>;
  submission?: Maybe<Submission>;
  type?: Maybe<Scalars['String']['output']>;
  url?: Maybe<Scalars['String']['output']>;
  user?: Maybe<User>;
};

/** A paginated list of Notification items. */
export type NotificationPaginator = {
  __typename?: 'NotificationPaginator';
  /** A list of Notification items. */
  data: Array<Notification>;
  /** Pagination information about the list of items. */
  paginatorInfo: PaginatorInfo;
};

/** Allows ordering a list of records. */
export type OrderByClause = {
  /** The column that is used for ordering. */
  column: Scalars['String']['input'];
  /** The direction that is used for ordering. */
  order: SortOrder;
};

/** Aggregate functions when ordering by a relation without specifying a column. */
export enum OrderByRelationAggregateFunction {
  /** Amount of items. */
  Count = 'COUNT'
}

/** Aggregate functions when ordering by a relation that may specify a column. */
export enum OrderByRelationWithColumnAggregateFunction {
  /** Average. */
  Avg = 'AVG',
  /** Amount of items. */
  Count = 'COUNT',
  /** Maximum. */
  Max = 'MAX',
  /** Minimum. */
  Min = 'MIN',
  /** Sum. */
  Sum = 'SUM'
}

/** An overall comment of a submission */
export type OverallComment = Comment & {
  __typename?: 'OverallComment';
  content: Scalars['String']['output'];
  created_at: Scalars['DateTimeUtc']['output'];
  created_by: User;
  deleted_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  id: Scalars['ID']['output'];
  read_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  replies?: Maybe<Array<OverallCommentReply>>;
  updated_at: Scalars['DateTimeUtc']['output'];
  updated_by: User;
};


/** An overall comment of a submission */
export type OverallCommentRepliesArgs = {
  trashed?: InputMaybe<Trashed>;
};

export type OverallCommentHasManyInput = {
  create?: InputMaybe<Array<CreateOverallCommentInput>>;
  update?: InputMaybe<Array<UpdateOverallCommentInput>>;
};

/** A reply to an overall comment of a submission */
export type OverallCommentReply = Comment & {
  __typename?: 'OverallCommentReply';
  content: Scalars['String']['output'];
  created_at: Scalars['DateTimeUtc']['output'];
  created_by: User;
  deleted_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  id: Scalars['ID']['output'];
  parent_id: Scalars['ID']['output'];
  read_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  reply_to_id: Scalars['ID']['output'];
  updated_at: Scalars['DateTimeUtc']['output'];
  updated_by: User;
};

/** Information about pagination using a fully featured paginator. */
export type PaginatorInfo = {
  __typename?: 'PaginatorInfo';
  /** Number of items in the current page. */
  count: Scalars['Int']['output'];
  /** Index of the current page. */
  currentPage: Scalars['Int']['output'];
  /** Index of the first item in the current page. */
  firstItem?: Maybe<Scalars['Int']['output']>;
  /** Are there more pages after this one? */
  hasMorePages: Scalars['Boolean']['output'];
  /** Index of the last item in the current page. */
  lastItem?: Maybe<Scalars['Int']['output']>;
  /** Index of the last available page. */
  lastPage: Scalars['Int']['output'];
  /** Number of items per page. */
  perPage: Scalars['Int']['output'];
  /** Number of total available items. */
  total: Scalars['Int']['output'];
};

/** A permission for roles and users */
export type Permission = {
  __typename?: 'Permission';
  created_at?: Maybe<Scalars['DateTime']['output']>;
  guard_name: Scalars['String']['output'];
  id: Scalars['ID']['output'];
  name: Scalars['String']['output'];
  updated_at?: Maybe<Scalars['DateTime']['output']>;
};

/** Profile metadata of a user */
export type ProfileMetadata = {
  __typename?: 'ProfileMetadata';
  academic_profiles?: Maybe<AcademicProfiles>;
  affiliation?: Maybe<Scalars['String']['output']>;
  biography?: Maybe<Scalars['String']['output']>;
  position_title?: Maybe<Scalars['String']['output']>;
  social_media?: Maybe<SocialMedia>;
  specialization?: Maybe<Scalars['String']['output']>;
  websites?: Maybe<Array<Maybe<Scalars['String']['output']>>>;
};

/** The provider within the response from an external identity provider */
export type Provider = {
  __typename?: 'Provider';
  provider_id: Scalars['String']['output'];
  provider_name: Scalars['String']['output'];
  user_id: Scalars['ID']['output'];
};

export type ProviderInput = {
  provider_id: Scalars['String']['input'];
  provider_name: Scalars['String']['input'];
};

/** The user within the response from an external identity provider */
export type ProviderUser = {
  __typename?: 'ProviderUser';
  email?: Maybe<Scalars['String']['output']>;
  name?: Maybe<Scalars['String']['output']>;
  username?: Maybe<Scalars['String']['output']>;
};

export type ProviderUserInput = {
  email?: InputMaybe<Scalars['String']['input']>;
  name?: InputMaybe<Scalars['String']['input']>;
  username?: InputMaybe<Scalars['String']['input']>;
};

/** A publication that has users, reviews, and submissions */
export type Publication = {
  __typename?: 'Publication';
  created_at?: Maybe<Scalars['DateTime']['output']>;
  editors: Array<User>;
  effective_role?: Maybe<PublicationRole>;
  home_page_content?: Maybe<Scalars['String']['output']>;
  id: Scalars['ID']['output'];
  is_accepting_submissions?: Maybe<Scalars['Boolean']['output']>;
  is_publicly_visible?: Maybe<Scalars['Boolean']['output']>;
  meta_form?: Maybe<MetaForm>;
  meta_forms?: Maybe<Array<MetaForm>>;
  my_role?: Maybe<PublicationRole>;
  name: Scalars['String']['output'];
  new_submission_content?: Maybe<Scalars['String']['output']>;
  publication_admins: Array<User>;
  style_criterias: Array<StyleCriteria>;
  submissions: Array<Maybe<Submission>>;
  updated_at?: Maybe<Scalars['DateTime']['output']>;
};


/** A publication that has users, reviews, and submissions */
export type PublicationMeta_FormArgs = {
  id: Scalars['ID']['input'];
};

/** A paginated list of Publication items. */
export type PublicationPaginator = {
  __typename?: 'PublicationPaginator';
  /** A list of Publication items. */
  data: Array<Publication>;
  /** Pagination information about the list of items. */
  paginatorInfo: PaginatorInfo;
};

export enum PublicationRole {
  Editor = 'editor',
  PublicationAdmin = 'publication_admin'
}

export type Query = {
  __typename?: 'Query';
  /** Return information about the currently logged in user */
  currentUser?: Maybe<User>;
  generalSettings?: Maybe<GeneralSettings>;
  identityProviders?: Maybe<Array<Maybe<IdentityProvider>>>;
  /** Return a permission by ID */
  permission?: Maybe<Permission>;
  /** Return a publication by ID */
  publication?: Maybe<Publication>;
  /** Return all publications */
  publications: PublicationPaginator;
  /** Return pre-defined user roles in the application */
  role?: Maybe<Role>;
  /** Return a submission by ID */
  submission?: Maybe<Submission>;
  /** Return all submissions */
  submissions: SubmissionPaginator;
  /** Return details about a specific user in the application */
  user?: Maybe<User>;
  /** Return user accounts optionally based on a supplied search term */
  userSearch: UserPaginator;
  /** Validate details of a new user.  Returns true if supplied user fields would be valid. */
  validateNewUser?: Maybe<Scalars['Boolean']['output']>;
  versionInfo?: Maybe<VersionInfo>;
};


export type QueryPermissionArgs = {
  id?: InputMaybe<Scalars['ID']['input']>;
};


export type QueryPublicationArgs = {
  id?: InputMaybe<Scalars['ID']['input']>;
};


export type QueryPublicationsArgs = {
  first?: Scalars['Int']['input'];
  is_accepting_submissions?: InputMaybe<Scalars['Boolean']['input']>;
  is_publicly_visible?: InputMaybe<Scalars['Boolean']['input']>;
  page?: InputMaybe<Scalars['Int']['input']>;
};


export type QueryRoleArgs = {
  id?: InputMaybe<Scalars['ID']['input']>;
};


export type QuerySubmissionArgs = {
  id?: InputMaybe<Scalars['ID']['input']>;
};


export type QuerySubmissionsArgs = {
  first?: Scalars['Int']['input'];
  page?: InputMaybe<Scalars['Int']['input']>;
};


export type QueryUserArgs = {
  id?: InputMaybe<Scalars['ID']['input']>;
};


export type QueryUserSearchArgs = {
  first?: Scalars['Int']['input'];
  page?: InputMaybe<Scalars['Int']['input']>;
  term?: InputMaybe<Scalars['String']['input']>;
};


export type QueryValidateNewUserArgs = {
  user?: InputMaybe<ValidateNewUserInput>;
};

/** Inputs */
export type RegisterOauthUserInput = {
  provider?: InputMaybe<ProviderInput>;
  user?: InputMaybe<ProviderUserInput>;
};

/** Input type for resetting a password from a password reset request email */
export type ResetPasswordInput = {
  email: Scalars['String']['input'];
  password: Scalars['String']['input'];
  token: Scalars['String']['input'];
};

/** A user role for permissions */
export type Role = {
  __typename?: 'Role';
  created_at?: Maybe<Scalars['DateTime']['output']>;
  guard_name: Scalars['String']['output'];
  id: Scalars['ID']['output'];
  name: Scalars['String']['output'];
  permissions?: Maybe<Array<Permission>>;
  updated_at?: Maybe<Scalars['DateTime']['output']>;
};

/** Social media data for the profile metadata of a user */
export type SocialMedia = {
  __typename?: 'SocialMedia';
  facebook?: Maybe<Scalars['String']['output']>;
  google?: Maybe<Scalars['String']['output']>;
  instagram?: Maybe<Scalars['String']['output']>;
  linkedin?: Maybe<Scalars['String']['output']>;
  twitter?: Maybe<Scalars['String']['output']>;
};

/** Directions for ordering a list of records. */
export enum SortOrder {
  /** Sort records in ascending order. */
  Asc = 'ASC',
  /** Sort records in descending order. */
  Desc = 'DESC'
}

export type StyleCriteria = {
  __typename?: 'StyleCriteria';
  description?: Maybe<Scalars['String']['output']>;
  icon?: Maybe<Scalars['String']['output']>;
  id: Scalars['ID']['output'];
  name: Scalars['String']['output'];
  publication: Publication;
};

/** A Submission */
export type Submission = {
  __typename?: 'Submission';
  audits?: Maybe<Array<Maybe<SubmissionAudit>>>;
  content?: Maybe<SubmissionContent>;
  created_at: Scalars['DateTimeUtc']['output'];
  created_by: User;
  effective_role?: Maybe<SubmissionUserRoles>;
  files: Array<Maybe<SubmissionFile>>;
  id: Scalars['ID']['output'];
  inline_comments: Array<InlineComment>;
  meta_forms?: Maybe<Array<MetaForm>>;
  meta_responses: Array<SubmissionMetaResponse>;
  my_role?: Maybe<SubmissionUserRoles>;
  overall_comments: Array<OverallComment>;
  pivot?: Maybe<SubmissionUser>;
  publication: Publication;
  review_coordinators: Array<User>;
  reviewers: Array<User>;
  status: SubmissionStatus;
  status_change_comment?: Maybe<Scalars['String']['output']>;
  submitted_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  submitters: Array<User>;
  title?: Maybe<Scalars['String']['output']>;
  updated_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  updated_by: User;
};


/** A Submission */
export type SubmissionInline_CommentsArgs = {
  trashed?: InputMaybe<Trashed>;
};


/** A Submission */
export type SubmissionOverall_CommentsArgs = {
  trashed?: InputMaybe<Trashed>;
};

export type SubmissionAudit = {
  __typename?: 'SubmissionAudit';
  created_at: Scalars['DateTimeUtc']['output'];
  event?: Maybe<Scalars['String']['output']>;
  id?: Maybe<Scalars['ID']['output']>;
  new_values?: Maybe<SubmissionAuditValues>;
  old_values?: Maybe<SubmissionAuditValues>;
  updated_at?: Maybe<Scalars['DateTimeUtc']['output']>;
  user?: Maybe<User>;
};

export type SubmissionAuditValues = {
  __typename?: 'SubmissionAuditValues';
  content_id?: Maybe<Scalars['ID']['output']>;
  status?: Maybe<SubmissionStatus>;
  status_change_comment?: Maybe<Scalars['String']['output']>;
  title?: Maybe<Scalars['String']['output']>;
};

/** The content of a submission */
export type SubmissionContent = {
  __typename?: 'SubmissionContent';
  created_at: Scalars['DateTimeUtc']['output'];
  data: Scalars['String']['output'];
  id: Scalars['ID']['output'];
  modified_at: Scalars['DateTimeUtc']['output'];
  src_file: SubmissionFile;
};

/** An uploaded file associated with a submission */
export type SubmissionFile = {
  __typename?: 'SubmissionFile';
  file_upload: Scalars['String']['output'];
  id: Scalars['ID']['output'];
  submission_id: Scalars['ID']['output'];
};

export type SubmissionMetaFormUpdate = {
  meta_form_id: Scalars['ID']['input'];
  responses: Array<SubmissionMetaPromptResponseInput>;
  submission_id: Scalars['ID']['input'];
};

export type SubmissionMetaPromptResponse = {
  __typename?: 'SubmissionMetaPromptResponse';
  meta_prompt_id: Scalars['ID']['output'];
  response?: Maybe<Scalars['String']['output']>;
};

export type SubmissionMetaPromptResponseInput = {
  meta_prompt_id: Scalars['ID']['input'];
  response?: InputMaybe<Scalars['String']['input']>;
};

export type SubmissionMetaResponse = {
  __typename?: 'SubmissionMetaResponse';
  id: Scalars['ID']['output'];
  meta_form?: Maybe<MetaForm>;
  name: Scalars['String']['output'];
  prompts?: Maybe<Scalars['JSON']['output']>;
  responses: Array<SubmissionMetaPromptResponse>;
};

/** A paginated list of Submission items. */
export type SubmissionPaginator = {
  __typename?: 'SubmissionPaginator';
  /** A list of Submission items. */
  data: Array<Submission>;
  /** Pagination information about the list of items. */
  paginatorInfo: PaginatorInfo;
};

/** The status of a submission */
export enum SubmissionStatus {
  AcceptedAsFinal = 'ACCEPTED_AS_FINAL',
  Archived = 'ARCHIVED',
  AwaitingDecision = 'AWAITING_DECISION',
  AwaitingReview = 'AWAITING_REVIEW',
  Deleted = 'DELETED',
  Draft = 'DRAFT',
  Expired = 'EXPIRED',
  InitiallySubmitted = 'INITIALLY_SUBMITTED',
  Rejected = 'REJECTED',
  ResubmissionRequested = 'RESUBMISSION_REQUESTED',
  Resubmitted = 'RESUBMITTED',
  RevisionRequested = 'REVISION_REQUESTED',
  UnderReview = 'UNDER_REVIEW'
}

/** A user associated with a submission and a role */
export type SubmissionUser = {
  __typename?: 'SubmissionUser';
  id: Scalars['ID']['output'];
  role_id: Scalars['ID']['output'];
  submission_id: Scalars['ID']['output'];
  user_id: Scalars['ID']['output'];
};

export enum SubmissionUserRoles {
  ReviewCoordinator = 'review_coordinator',
  Reviewer = 'reviewer',
  Submitter = 'submitter'
}

/** Specify if you want to include or exclude trashed results from a query. */
export enum Trashed {
  /** Only return trashed results. */
  Only = 'ONLY',
  /** Return both trashed and non-trashed results. */
  With = 'WITH',
  /** Only return non-trashed results. */
  Without = 'WITHOUT'
}

export type UpdateAcademicProfilesInput = {
  humanities_commons?: InputMaybe<Scalars['String']['input']>;
  orcid_id?: InputMaybe<Scalars['String']['input']>;
};

export type UpdateInlineCommentInput = {
  content?: InputMaybe<Scalars['String']['input']>;
  from?: InputMaybe<Scalars['Int']['input']>;
  id: Scalars['ID']['input'];
  style_criteria?: InputMaybe<Array<Scalars['ID']['input']>>;
  to?: InputMaybe<Scalars['Int']['input']>;
};

export type UpdateMetaFormInput = {
  caption?: InputMaybe<Scalars['String']['input']>;
  id: Scalars['ID']['input'];
  name?: InputMaybe<Scalars['String']['input']>;
  required?: InputMaybe<Scalars['Boolean']['input']>;
};

export type UpdateMetaPromptInput = {
  caption?: InputMaybe<Scalars['String']['input']>;
  id: Scalars['ID']['input'];
  label?: InputMaybe<Scalars['String']['input']>;
  options?: InputMaybe<Scalars['JSON']['input']>;
  order?: InputMaybe<Scalars['Int']['input']>;
  required?: InputMaybe<Scalars['Boolean']['input']>;
  type?: InputMaybe<MetaPromptType>;
};

export type UpdateOverallCommentInput = {
  content?: InputMaybe<Scalars['String']['input']>;
  id: Scalars['ID']['input'];
};

/** Input type for adding profile metadata for a user */
export type UpdateProfileMetadataInput = {
  academic_profiles?: InputMaybe<UpdateAcademicProfilesInput>;
  affiliation?: InputMaybe<Scalars['String']['input']>;
  biography?: InputMaybe<Scalars['String']['input']>;
  position_title?: InputMaybe<Scalars['String']['input']>;
  social_media?: InputMaybe<UpdateSocialMediaInput>;
  specialization?: InputMaybe<Scalars['String']['input']>;
  websites?: InputMaybe<Array<InputMaybe<Scalars['String']['input']>>>;
};

export type UpdatePublicationAdministratorsInput = {
  connect?: InputMaybe<Array<Scalars['ID']['input']>>;
  disconnect?: InputMaybe<Array<Scalars['ID']['input']>>;
};

export type UpdatePublicationEditorsInput = {
  connect?: InputMaybe<Array<Scalars['ID']['input']>>;
  disconnect?: InputMaybe<Array<Scalars['ID']['input']>>;
};

export type UpdatePublicationInput = {
  description?: InputMaybe<Scalars['String']['input']>;
  editors?: InputMaybe<UpdatePublicationEditorsInput>;
  home_page_content?: InputMaybe<Scalars['String']['input']>;
  id: Scalars['ID']['input'];
  is_accepting_submissions?: InputMaybe<Scalars['Boolean']['input']>;
  is_publicly_visible?: InputMaybe<Scalars['Boolean']['input']>;
  name?: InputMaybe<Scalars['String']['input']>;
  new_submission_content?: InputMaybe<Scalars['String']['input']>;
  publication_admins?: InputMaybe<UpdatePublicationAdministratorsInput>;
  style_criterias?: InputMaybe<UpdateStyleCriteriaHasMany>;
};

/** Input type for adding social media data to profile metadata */
export type UpdateSocialMediaInput = {
  facebook?: InputMaybe<Scalars['String']['input']>;
  google?: InputMaybe<Scalars['String']['input']>;
  instagram?: InputMaybe<Scalars['String']['input']>;
  linkedin?: InputMaybe<Scalars['String']['input']>;
  twitter?: InputMaybe<Scalars['String']['input']>;
};

export type UpdateStyleCriteriaHasMany = {
  create?: InputMaybe<Array<CreateStyleCriteriaInput>>;
  delete?: InputMaybe<Array<Scalars['ID']['input']>>;
  update?: InputMaybe<Array<UpdateStyleCriteriaInput>>;
};

export type UpdateStyleCriteriaInput = {
  description?: InputMaybe<Scalars['String']['input']>;
  icon?: InputMaybe<Scalars['String']['input']>;
  id: Scalars['ID']['input'];
  name?: InputMaybe<Scalars['String']['input']>;
};

export type UpdateSubmissionContent = {
  content: Scalars['String']['input'];
  id: Scalars['ID']['input'];
};

export type UpdateSubmissionInput = {
  id: Scalars['ID']['input'];
  inline_comments?: InputMaybe<InlineCommentHasManyInput>;
  overall_comments?: InputMaybe<OverallCommentHasManyInput>;
  review_coordinators?: InputMaybe<UpdateSubmissionUserInput>;
  reviewers?: InputMaybe<UpdateSubmissionUserInput>;
  status?: InputMaybe<SubmissionStatus>;
  status_change_comment?: InputMaybe<Scalars['String']['input']>;
  submitters?: InputMaybe<UpdateSubmissionUserInput>;
  title?: InputMaybe<Scalars['String']['input']>;
};

export type UpdateSubmissionUserInput = {
  connect?: InputMaybe<Array<Scalars['ID']['input']>>;
  disconnect?: InputMaybe<Array<Scalars['ID']['input']>>;
};

/** Input type for updating user information via the updateUser mutation */
export type UpdateUserInput = {
  /** Email address of the user. Must be unique. */
  email?: InputMaybe<Scalars['String']['input']>;
  /** User ID */
  id: Scalars['ID']['input'];
  /** Display name */
  name?: InputMaybe<Scalars['String']['input']>;
  /** Password used to login to the application. */
  password?: InputMaybe<Scalars['String']['input']>;
  /** Metadata related to the profile of a user. */
  profile_metadata?: InputMaybe<UpdateProfileMetadataInput>;
  /** Username of the user. Must be unique. */
  username?: InputMaybe<Scalars['String']['input']>;
};

/** A user account */
export type User = {
  __typename?: 'User';
  created_at: Scalars['DateTime']['output'];
  display_label?: Maybe<Scalars['String']['output']>;
  email: Scalars['String']['output'];
  email_verified_at?: Maybe<Scalars['DateTime']['output']>;
  highest_privileged_role?: Maybe<UserRoles>;
  id: Scalars['ID']['output'];
  name?: Maybe<Scalars['String']['output']>;
  notifications: NotificationPaginator;
  permissions?: Maybe<Array<Permission>>;
  pivot?: Maybe<SubmissionUser>;
  profile_metadata?: Maybe<ProfileMetadata>;
  roles: Array<Role>;
  staged?: Maybe<Scalars['Boolean']['output']>;
  submissions: Array<Maybe<Submission>>;
  updated_at?: Maybe<Scalars['DateTime']['output']>;
  username: Scalars['String']['output'];
};


/** A user account */
export type UserNotificationsArgs = {
  first: Scalars['Int']['input'];
  page?: InputMaybe<Scalars['Int']['input']>;
  read?: InputMaybe<Scalars['Boolean']['input']>;
  unread?: InputMaybe<Scalars['Boolean']['input']>;
};

/** A paginated list of User items. */
export type UserPaginator = {
  __typename?: 'UserPaginator';
  /** A list of User items. */
  data: Array<User>;
  /** Pagination information about the list of items. */
  paginatorInfo: PaginatorInfo;
};

export enum UserRoles {
  ApplicationAdmin = 'application_admin',
  Editor = 'editor',
  PublicationAdmin = 'publication_admin',
  ReviewCoordinator = 'review_coordinator',
  Reviewer = 'reviewer',
  Submitter = 'submitter'
}

/** Validate the availability of username and email via the validateNewUser query */
export type ValidateNewUserInput = {
  /** Email. Validation error if not unique */
  email?: InputMaybe<Scalars['String']['input']>;
  /** Username.  Validation error if not unique */
  username?: InputMaybe<Scalars['String']['input']>;
};

export type VersionInfo = {
  __typename?: 'VersionInfo';
  version?: Maybe<Scalars['String']['output']>;
  version_date?: Maybe<Scalars['String']['output']>;
  version_url?: Maybe<Scalars['String']['output']>;
};

export type InviteSubmissionUserInput = {
  email: Scalars['String']['input'];
  message?: InputMaybe<Scalars['String']['input']>;
  submission_id: Scalars['ID']['input'];
};

export type ReinviteSubmissionUserInput = {
  email: Scalars['String']['input'];
  message?: InputMaybe<Scalars['String']['input']>;
  submission_id: Scalars['ID']['input'];
};

export type PaginationFieldsFragment = { __typename?: 'PaginatorInfo', count: number, currentPage: number, lastPage: number, perPage: number } & { ' $fragmentName'?: 'PaginationFieldsFragment' };

export type CurrentUserSubmissionsQueryVariables = Exact<{ [key: string]: never; }>;


export type CurrentUserSubmissionsQuery = { __typename?: 'Query', currentUser?: { __typename?: 'User', id: string, roles: Array<{ __typename?: 'Role', name: string }>, submissions: Array<{ __typename?: 'Submission', id: string, title?: string | null, status: SubmissionStatus, created_at: any, submitted_at?: any | null, my_role?: SubmissionUserRoles | null, effective_role?: SubmissionUserRoles | null, review_coordinators: Array<(
        { __typename?: 'User' }
        & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
      )>, reviewers: Array<(
        { __typename?: 'User' }
        & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
      )>, submitters: Array<(
        { __typename?: 'User' }
        & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
      )>, inline_comments: Array<{ __typename?: 'InlineComment', id: string, content: string, created_at: any, updated_at: any, read_at?: any | null, created_by: { __typename?: 'User', id: string, display_label?: string | null, email: string }, updated_by: { __typename?: 'User', id: string, display_label?: string | null, email: string }, style_criteria?: Array<{ __typename?: 'InlineCommentStyleCriteria', id?: string | null, name?: string | null, icon?: string | null }> | null, replies?: Array<{ __typename?: 'InlineCommentReply', id: string, content: string, created_at: any, updated_at: any, read_at?: any | null, created_by: { __typename?: 'User', id: string, display_label?: string | null, email: string }, updated_by: { __typename?: 'User', id: string, display_label?: string | null, email: string } }> | null }>, overall_comments: Array<{ __typename?: 'OverallComment', id: string, content: string, created_at: any, updated_at: any, read_at?: any | null, created_by: { __typename?: 'User', id: string, display_label?: string | null, email: string }, updated_by: { __typename?: 'User', id: string, display_label?: string | null, email: string }, replies?: Array<{ __typename?: 'OverallCommentReply', id: string, content: string, created_at: any, updated_at: any, read_at?: any | null, created_by: { __typename?: 'User', id: string, display_label?: string | null, email: string }, updated_by: { __typename?: 'User', id: string, display_label?: string | null, email: string } }> | null }>, publication: { __typename?: 'Publication', id: string, name: string, my_role?: PublicationRole | null, editors: Array<(
          { __typename?: 'User' }
          & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
        )>, publication_admins: Array<(
          { __typename?: 'User' }
          & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
        )> } } | null> } | null };

export type RelatedUserFieldsFragment = { __typename?: 'User', id: string, display_label?: string | null, username: string, name?: string | null, email: string, staged?: boolean | null } & { ' $fragmentName'?: 'RelatedUserFieldsFragment' };

export type GetSubmissionsQueryVariables = Exact<{
  page?: InputMaybe<Scalars['Int']['input']>;
}>;


export type GetSubmissionsQuery = { __typename?: 'Query', submissions: { __typename?: 'SubmissionPaginator', paginatorInfo: (
      { __typename?: 'PaginatorInfo' }
      & { ' $fragmentRefs'?: { 'PaginationFieldsFragment': PaginationFieldsFragment } }
    ), data: Array<{ __typename?: 'Submission', id: string, title?: string | null, status: SubmissionStatus, my_role?: SubmissionUserRoles | null, created_at: any, submitted_at?: any | null, effective_role?: SubmissionUserRoles | null, submitters: Array<(
        { __typename?: 'User' }
        & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
      )>, reviewers: Array<(
        { __typename?: 'User' }
        & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
      )>, review_coordinators: Array<(
        { __typename?: 'User' }
        & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
      )>, publication: { __typename?: 'Publication', id: string, name: string, my_role?: PublicationRole | null, editors: Array<(
          { __typename?: 'User' }
          & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
        )>, publication_admins: Array<(
          { __typename?: 'User' }
          & { ' $fragmentRefs'?: { 'RelatedUserFieldsFragment': RelatedUserFieldsFragment } }
        )> } }> } };

export type GetPublicationPromptsQueryVariables = Exact<{
  id: Scalars['ID']['input'];
}>;


export type GetPublicationPromptsQuery = { __typename?: 'Query', publication?: { __typename?: 'Publication', id: string, meta_forms?: Array<{ __typename?: 'MetaForm', id: string, name: string, caption?: string | null, required: boolean, meta_prompts?: Array<{ __typename?: 'MetaPrompt', id: string, label: string, type: MetaPromptType, order: number, options?: any | null, required: boolean, caption?: string | null }> | null }> | null } | null };

export type MetaPromptUpdateMutationVariables = Exact<{
  input: Array<UpdateMetaPromptInput> | UpdateMetaPromptInput;
}>;


export type MetaPromptUpdateMutation = { __typename?: 'Mutation', metaPromptUpdate: Array<{ __typename?: 'MetaPrompt', id: string, label: string, order: number, required: boolean, type: MetaPromptType, options?: any | null, caption?: string | null }> };

export type SubmissionMetaFormsQueryVariables = Exact<{
  id: Scalars['ID']['input'];
  formId: Scalars['ID']['input'];
}>;


export type SubmissionMetaFormsQuery = { __typename?: 'Query', submission?: { __typename?: 'Submission', id: string, title?: string | null, publication: { __typename?: 'Publication', meta_form?: { __typename?: 'MetaForm', id: string, name: string, meta_prompts?: Array<{ __typename?: 'MetaPrompt', id: string, label: string, type: MetaPromptType, options?: any | null }> | null } | null } } | null };

export const PaginationFieldsFragmentDoc = {"kind":"Document","definitions":[{"kind":"FragmentDefinition","name":{"kind":"Name","value":"PaginationFields"},"typeCondition":{"kind":"NamedType","name":{"kind":"Name","value":"PaginatorInfo"}},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"count"}},{"kind":"Field","name":{"kind":"Name","value":"currentPage"}},{"kind":"Field","name":{"kind":"Name","value":"lastPage"}},{"kind":"Field","name":{"kind":"Name","value":"perPage"}}]}}]} as unknown as DocumentNode<PaginationFieldsFragment, unknown>;
export const RelatedUserFieldsFragmentDoc = {"kind":"Document","definitions":[{"kind":"FragmentDefinition","name":{"kind":"Name","value":"RelatedUserFields"},"typeCondition":{"kind":"NamedType","name":{"kind":"Name","value":"User"}},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"username"}},{"kind":"Field","name":{"kind":"Name","value":"name"}},{"kind":"Field","name":{"kind":"Name","value":"email"}},{"kind":"Field","name":{"kind":"Name","value":"staged"}}]}}]} as unknown as DocumentNode<RelatedUserFieldsFragment, unknown>;
export const CurrentUserSubmissionsDocument = {"kind":"Document","definitions":[{"kind":"OperationDefinition","operation":"query","name":{"kind":"Name","value":"CurrentUserSubmissions"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"currentUser"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"roles"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"name"}}]}},{"kind":"Field","name":{"kind":"Name","value":"submissions"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"title"}},{"kind":"Field","name":{"kind":"Name","value":"status"}},{"kind":"Field","name":{"kind":"Name","value":"created_at"}},{"kind":"Field","name":{"kind":"Name","value":"submitted_at"}},{"kind":"Field","name":{"kind":"Name","value":"my_role"}},{"kind":"Field","name":{"kind":"Name","value":"effective_role"}},{"kind":"Field","name":{"kind":"Name","value":"review_coordinators"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"reviewers"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"submitters"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"inline_comments"},"arguments":[{"kind":"Argument","name":{"kind":"Name","value":"trashed"},"value":{"kind":"EnumValue","value":"WITH"}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"content"}},{"kind":"Field","name":{"kind":"Name","value":"created_by"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"email"}}]}},{"kind":"Field","name":{"kind":"Name","value":"updated_by"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"email"}}]}},{"kind":"Field","name":{"kind":"Name","value":"created_at"}},{"kind":"Field","name":{"kind":"Name","value":"updated_at"}},{"kind":"Field","name":{"kind":"Name","value":"style_criteria"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"name"}},{"kind":"Field","name":{"kind":"Name","value":"icon"}}]}},{"kind":"Field","name":{"kind":"Name","value":"replies"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"content"}},{"kind":"Field","name":{"kind":"Name","value":"created_by"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"email"}}]}},{"kind":"Field","name":{"kind":"Name","value":"updated_by"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"email"}}]}},{"kind":"Field","name":{"kind":"Name","value":"created_at"}},{"kind":"Field","name":{"kind":"Name","value":"updated_at"}},{"kind":"Field","name":{"kind":"Name","value":"read_at"}}]}},{"kind":"Field","name":{"kind":"Name","value":"read_at"}}]}},{"kind":"Field","name":{"kind":"Name","value":"overall_comments"},"arguments":[{"kind":"Argument","name":{"kind":"Name","value":"trashed"},"value":{"kind":"EnumValue","value":"WITH"}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"content"}},{"kind":"Field","name":{"kind":"Name","value":"created_by"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"email"}}]}},{"kind":"Field","name":{"kind":"Name","value":"updated_by"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"email"}}]}},{"kind":"Field","name":{"kind":"Name","value":"created_at"}},{"kind":"Field","name":{"kind":"Name","value":"updated_at"}},{"kind":"Field","name":{"kind":"Name","value":"replies"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"content"}},{"kind":"Field","name":{"kind":"Name","value":"created_by"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"email"}}]}},{"kind":"Field","name":{"kind":"Name","value":"updated_by"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"email"}}]}},{"kind":"Field","name":{"kind":"Name","value":"created_at"}},{"kind":"Field","name":{"kind":"Name","value":"updated_at"}},{"kind":"Field","name":{"kind":"Name","value":"read_at"}}]}},{"kind":"Field","name":{"kind":"Name","value":"read_at"}}]}},{"kind":"Field","name":{"kind":"Name","value":"publication"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"name"}},{"kind":"Field","name":{"kind":"Name","value":"my_role"}},{"kind":"Field","name":{"kind":"Name","value":"editors"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"publication_admins"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}}]}}]}}]}}]}},{"kind":"FragmentDefinition","name":{"kind":"Name","value":"RelatedUserFields"},"typeCondition":{"kind":"NamedType","name":{"kind":"Name","value":"User"}},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"username"}},{"kind":"Field","name":{"kind":"Name","value":"name"}},{"kind":"Field","name":{"kind":"Name","value":"email"}},{"kind":"Field","name":{"kind":"Name","value":"staged"}}]}}]} as unknown as DocumentNode<CurrentUserSubmissionsQuery, CurrentUserSubmissionsQueryVariables>;
export const GetSubmissionsDocument = {"kind":"Document","definitions":[{"kind":"OperationDefinition","operation":"query","name":{"kind":"Name","value":"GetSubmissions"},"variableDefinitions":[{"kind":"VariableDefinition","variable":{"kind":"Variable","name":{"kind":"Name","value":"page"}},"type":{"kind":"NamedType","name":{"kind":"Name","value":"Int"}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"submissions"},"arguments":[{"kind":"Argument","name":{"kind":"Name","value":"page"},"value":{"kind":"Variable","name":{"kind":"Name","value":"page"}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"paginatorInfo"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"PaginationFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"data"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"title"}},{"kind":"Field","name":{"kind":"Name","value":"status"}},{"kind":"Field","name":{"kind":"Name","value":"my_role"}},{"kind":"Field","name":{"kind":"Name","value":"created_at"}},{"kind":"Field","name":{"kind":"Name","value":"submitted_at"}},{"kind":"Field","name":{"kind":"Name","value":"effective_role"}},{"kind":"Field","name":{"kind":"Name","value":"submitters"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"reviewers"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"review_coordinators"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"publication"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"name"}},{"kind":"Field","name":{"kind":"Name","value":"my_role"}},{"kind":"Field","name":{"kind":"Name","value":"editors"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}},{"kind":"Field","name":{"kind":"Name","value":"publication_admins"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"FragmentSpread","name":{"kind":"Name","value":"RelatedUserFields"}}]}}]}}]}}]}}]}},{"kind":"FragmentDefinition","name":{"kind":"Name","value":"PaginationFields"},"typeCondition":{"kind":"NamedType","name":{"kind":"Name","value":"PaginatorInfo"}},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"count"}},{"kind":"Field","name":{"kind":"Name","value":"currentPage"}},{"kind":"Field","name":{"kind":"Name","value":"lastPage"}},{"kind":"Field","name":{"kind":"Name","value":"perPage"}}]}},{"kind":"FragmentDefinition","name":{"kind":"Name","value":"RelatedUserFields"},"typeCondition":{"kind":"NamedType","name":{"kind":"Name","value":"User"}},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"display_label"}},{"kind":"Field","name":{"kind":"Name","value":"username"}},{"kind":"Field","name":{"kind":"Name","value":"name"}},{"kind":"Field","name":{"kind":"Name","value":"email"}},{"kind":"Field","name":{"kind":"Name","value":"staged"}}]}}]} as unknown as DocumentNode<GetSubmissionsQuery, GetSubmissionsQueryVariables>;
export const GetPublicationPromptsDocument = {"kind":"Document","definitions":[{"kind":"OperationDefinition","operation":"query","name":{"kind":"Name","value":"GetPublicationPrompts"},"variableDefinitions":[{"kind":"VariableDefinition","variable":{"kind":"Variable","name":{"kind":"Name","value":"id"}},"type":{"kind":"NonNullType","type":{"kind":"NamedType","name":{"kind":"Name","value":"ID"}}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"publication"},"arguments":[{"kind":"Argument","name":{"kind":"Name","value":"id"},"value":{"kind":"Variable","name":{"kind":"Name","value":"id"}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"meta_forms"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"name"}},{"kind":"Field","name":{"kind":"Name","value":"caption"}},{"kind":"Field","name":{"kind":"Name","value":"required"}},{"kind":"Field","name":{"kind":"Name","value":"meta_prompts"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"label"}},{"kind":"Field","name":{"kind":"Name","value":"type"}},{"kind":"Field","name":{"kind":"Name","value":"order"}},{"kind":"Field","name":{"kind":"Name","value":"options"}},{"kind":"Field","name":{"kind":"Name","value":"required"}},{"kind":"Field","name":{"kind":"Name","value":"caption"}}]}}]}}]}}]}}]} as unknown as DocumentNode<GetPublicationPromptsQuery, GetPublicationPromptsQueryVariables>;
export const MetaPromptUpdateDocument = {"kind":"Document","definitions":[{"kind":"OperationDefinition","operation":"mutation","name":{"kind":"Name","value":"MetaPromptUpdate"},"variableDefinitions":[{"kind":"VariableDefinition","variable":{"kind":"Variable","name":{"kind":"Name","value":"input"}},"type":{"kind":"NonNullType","type":{"kind":"ListType","type":{"kind":"NonNullType","type":{"kind":"NamedType","name":{"kind":"Name","value":"UpdateMetaPromptInput"}}}}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"metaPromptUpdate"},"arguments":[{"kind":"Argument","name":{"kind":"Name","value":"input"},"value":{"kind":"Variable","name":{"kind":"Name","value":"input"}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"label"}},{"kind":"Field","name":{"kind":"Name","value":"order"}},{"kind":"Field","name":{"kind":"Name","value":"required"}},{"kind":"Field","name":{"kind":"Name","value":"type"}},{"kind":"Field","name":{"kind":"Name","value":"options"}},{"kind":"Field","name":{"kind":"Name","value":"caption"}}]}}]}}]} as unknown as DocumentNode<MetaPromptUpdateMutation, MetaPromptUpdateMutationVariables>;
export const SubmissionMetaFormsDocument = {"kind":"Document","definitions":[{"kind":"OperationDefinition","operation":"query","name":{"kind":"Name","value":"SubmissionMetaForms"},"variableDefinitions":[{"kind":"VariableDefinition","variable":{"kind":"Variable","name":{"kind":"Name","value":"id"}},"type":{"kind":"NonNullType","type":{"kind":"NamedType","name":{"kind":"Name","value":"ID"}}}},{"kind":"VariableDefinition","variable":{"kind":"Variable","name":{"kind":"Name","value":"formId"}},"type":{"kind":"NonNullType","type":{"kind":"NamedType","name":{"kind":"Name","value":"ID"}}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"submission"},"arguments":[{"kind":"Argument","name":{"kind":"Name","value":"id"},"value":{"kind":"Variable","name":{"kind":"Name","value":"id"}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"title"}},{"kind":"Field","name":{"kind":"Name","value":"publication"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"meta_form"},"arguments":[{"kind":"Argument","name":{"kind":"Name","value":"id"},"value":{"kind":"Variable","name":{"kind":"Name","value":"formId"}}}],"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"name"}},{"kind":"Field","name":{"kind":"Name","value":"meta_prompts"},"selectionSet":{"kind":"SelectionSet","selections":[{"kind":"Field","name":{"kind":"Name","value":"id"}},{"kind":"Field","name":{"kind":"Name","value":"label"}},{"kind":"Field","name":{"kind":"Name","value":"type"}},{"kind":"Field","name":{"kind":"Name","value":"options"}}]}}]}}]}}]}}]}}]} as unknown as DocumentNode<SubmissionMetaFormsQuery, SubmissionMetaFormsQueryVariables>;