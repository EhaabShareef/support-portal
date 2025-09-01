# Settings Module Refactor Plan

## 🎯 **Current Issues**

1. **Monolithic Components**: Each settings tab is 20KB+ files with mixed concerns
2. **Inconsistent Patterns**: Different approaches for similar functionality
3. **Hard to Debug**: Complex component interactions and state management
4. **Poor Separation**: Settings table vs. other data models mixed together
5. **No Reusability**: Each setting type requires custom implementation
6. **Difficult to Extend**: Adding new settings requires significant code changes

## 🚀 **Proposed New Architecture**

### **1. Base Components & Inheritance**

```
app/Livewire/Admin/Settings/
├── BaseSettingsComponent.php          # Abstract base class
├── Shell.php                          # Main settings shell
└── Modules/                           # Individual settings modules
    ├── GeneralSettings.php
    ├── TicketSettings.php
    ├── OrganizationSettings.php
    ├── ContractSettings.php
    ├── HardwareSettings.php
    ├── ScheduleSettings.php
    └── UserSettings.php
```

### **2. Reusable UI Components**

```
resources/views/components/settings/
├── base-layout.blade.php              # Base layout with header, save/reset buttons
├── section.blade.php                   # Settings section wrapper
├── form-field.blade.php                # Reusable form field component
├── modal.blade.php                     # Reusable modal component
└── status-card.blade.php               # Status management cards
```

### **3. Settings Registry System**

```
app/Services/
├── SettingsRegistry.php               # Dynamic module registration
└── SettingsRepository.php             # Enhanced with better caching
```

## 📋 **Implementation Steps**

### **Phase 1: Foundation (Week 1)**

1. **Create Base Components**
   - `BaseSettingsComponent` abstract class
   - Common UI components (base-layout, section, form-field)
   - Enhanced `SettingsRepository` with better caching

2. **Create Settings Registry**
   - Dynamic module registration system
   - Permission-based access control
   - Easy module discovery and loading

### **Phase 2: Core Modules (Week 2)**

1. **Refactor Existing Modules**
   - Convert each settings tab to use new base class
   - Implement consistent patterns across all modules
   - Add proper error handling and validation

2. **Create Modular Structure**
   - Separate business logic from UI
   - Implement reusable form components
   - Add proper state management

### **Phase 3: Enhanced Features (Week 3)**

1. **Advanced Features**
   - Settings import/export functionality
   - Settings templates and presets
   - Audit logging for settings changes
   - Settings validation and constraints

2. **Developer Experience**
   - Settings module generator
   - Documentation and examples
   - Testing utilities

## 🏗️ **New File Structure**

### **Option 1: Simple Settings (Single File)**
For settings with 2-3 simple sections:
```
app/Livewire/Admin/Settings/Modules/
├── GeneralSettings.php          # Single file with all sections
├── UserSettings.php             # Single file with all sections
├── ScheduleSettings.php         # Single file with all sections
├── OrganizationSettings.php     # Single file with all sections
├── ContractSettings.php         # Single file with all sections
└── HardwareSettings.php         # Single file with all sections
```

### **Option 2: Complex Settings (Multiple Files)**
For settings with 4+ complex sections like tickets:
```
app/Livewire/Admin/Settings/Modules/TicketSettings/
├── TicketSettings.php                    # Main component (orchestrator)
├── Sections/
│   ├── WorkflowSection.php              # Workflow settings
│   ├── AttachmentSection.php            # Attachment settings  
│   ├── PrioritySection.php              # Priority colors
│   └── StatusSection.php                # Status management
└── Modals/
    ├── AddStatusModal.php               # Add new status
    ├── EditStatusModal.php              # Edit status
    └── DepartmentAccessModal.php        # Department group access
```

### **Reusable Components**
```
resources/views/components/settings/
├── base-layout.blade.php              # Main layout wrapper
├── section.blade.php                   # Section wrapper
├── form-field.blade.php                # Reusable form fields
├── modal.blade.php                     # Reusable modals
└── status-card.blade.php               # Status management cards
```

### **Views Structure**
```
resources/views/livewire/admin/settings/
├── shell.blade.php                     # Main settings shell
└── modules/
    ├── general-settings.blade.php      # Simple settings view
    ├── user-settings.blade.php         # Simple settings view
    ├── schedule-settings.blade.php     # Simple settings view
    ├── organization-settings.blade.php # Simple settings view
    ├── contract-settings.blade.php     # Simple settings view
    ├── hardware-settings.blade.php     # Simple settings view
    └── ticket-settings/                # Complex settings folder
        ├── index.blade.php             # Main ticket settings view
        ├── sections/
        │   ├── workflow.blade.php      # Workflow section view
        │   ├── attachment.blade.php    # Attachment section view
        │   ├── priority.blade.php      # Priority section view
        │   └── status.blade.php        # Status section view
        └── modals/
            ├── add-status.blade.php    # Add status modal
            ├── edit-status.blade.php   # Edit status modal
            └── department-access.blade.php # Department access modal
```

### **Services**
```
app/Services/
├── SettingsRegistry.php               # Dynamic module registration
└── SettingsRepository.php             # Enhanced with better caching
```

## 🔧 **Key Benefits**

### **For Developers**
1. **Easy to Add New Settings**: Just extend `BaseSettingsComponent`
2. **Consistent Patterns**: All settings follow the same structure
3. **Better Debugging**: Clear separation of concerns
4. **Reusable Components**: Common UI elements shared across modules
5. **Type Safety**: Better validation and error handling

### **For Users**
1. **Consistent UI**: All settings pages look and behave the same
2. **Better Performance**: Improved caching and loading
3. **Enhanced Features**: Import/export, templates, audit logging
4. **Better Error Handling**: Clear error messages and validation

### **For Maintenance**
1. **Modular Code**: Easy to modify individual settings without affecting others
2. **Clear Structure**: Easy to understand and navigate
3. **Better Testing**: Isolated components are easier to test
4. **Documentation**: Self-documenting code structure

## 📝 **Example Usage**

### **Creating a New Settings Module**

```php
class NewSettings extends BaseSettingsComponent
{
    public string $someSetting = '';
    
    protected function getSettingsGroup(): string
    {
        return 'new_module';
    }
    
    protected function getTitle(): string
    {
        return 'New Settings';
    }
    
    protected function loadData(): void
    {
        $this->someSetting = $this->getSetting('new_module.some_setting', 'default');
    }
    
    protected function saveData(): void
    {
        $this->setSetting('new_module.some_setting', $this->someSetting);
    }
}
```

### **Registering the Module**

```php
// In a service provider
$registry = app(SettingsRegistry::class);
$registry->register('new_module', [
    'title' => 'New Module',
    'description' => 'New module settings',
    'component' => 'admin.settings.modules.new-settings',
    'sort_order' => 8,
]);
```

## 🎨 **UI Components**

### **Base Layout**
- Consistent header with title, description, and icon
- Save/Reset buttons with unsaved changes indicator
- Flash message handling
- Unsaved changes warning

### **Settings Section**
- Reusable section wrapper with title and description
- Consistent styling and spacing
- Optional icon support

### **Form Fields**
- Reusable form field components
- Built-in validation display
- Consistent styling and behavior

### **Modals**
- Reusable modal components
- Consistent styling and behavior
- Easy to implement for CRUD operations

## 📁 **Section Organization Strategy**

### **Option 1: Single File Approach**
For simple settings with 2-3 sections:
```php
class GeneralSettings extends BaseSettingsComponent
{
    // All sections in one file
    public string $appName = '';
    public string $appEmail = '';
    public array $hotlines = [];
    
    protected function loadData(): void
    {
        // Load all sections
    }
    
    protected function saveData(): void
    {
        // Save all sections
    }
}
```

### **Option 2: Multiple Files Approach**
For complex settings like tickets with 4+ sections:
```php
// Main component (orchestrator)
class TicketSettings extends BaseSettingsComponent
{
    public string $activeSection = 'workflow';
    public $workflowSection;
    public $attachmentSection;
    public $prioritySection;
    public $statusSection;
    
    protected function loadData(): void
    {
        // Initialize section components
        $this->workflowSection = new WorkflowSection();
        $this->attachmentSection = new AttachmentSection();
        // etc...
    }
}

// Individual section components
class WorkflowSection extends BaseSettingsComponent
{
    public string $defaultReplyStatus = '';
    public int $reopenWindowDays = 3;
    // Section-specific logic
}
```

### **Benefits of Each Approach**

**Single File:**
- ✅ Simpler to implement
- ✅ Easier to debug
- ✅ Less file management
- ❌ Can become large and unwieldy

**Multiple Files:**
- ✅ Better separation of concerns
- ✅ Easier to maintain individual sections
- ✅ Reusable section components
- ✅ Better for complex settings
- ❌ More files to manage
- ❌ Slightly more complex setup

## 🔄 **Migration Strategy**

1. **Phase 1**: Create new structure alongside existing
2. **Phase 2**: Migrate one module at a time
3. **Phase 3**: Remove old structure once all modules migrated
4. **Phase 4**: Add new features and enhancements

## 📊 **Success Metrics**

1. **Code Reduction**: 50% reduction in component file sizes
2. **Maintainability**: 80% reduction in time to add new settings
3. **Consistency**: 100% consistent UI/UX across all settings
4. **Performance**: 30% improvement in settings page load times
5. **Developer Experience**: 90% reduction in debugging time

## 🚀 **Next Steps**

1. **Approve Plan**: Review and approve the refactor plan
2. **Start Phase 1**: Create base components and registry
3. **Migrate Modules**: Convert existing settings one by one
4. **Add Features**: Implement advanced features and enhancements
5. **Documentation**: Create comprehensive documentation

This refactor will transform the settings module from a complex, hard-to-maintain system into a clean, modular, and extensible architecture that's easy to use and maintain.
