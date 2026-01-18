import { Compass, Clock, Sparkles } from 'lucide-react';
import { useInView } from '@/hooks/useInView';

const hooks = [
  {
    icon: Compass,
    question: 'مش عارف تبدأ منين؟',
    action: 'ابدأ بمسار قراءة',
    color: 'from-teal-500 to-teal-600',
    iconBg: 'bg-teal-100 text-teal-600',
  },
  {
    icon: Clock,
    question: 'عايز كتاب سريع؟',
    action: 'كتب قصيرة عظيمة',
    color: 'from-amber-500 to-orange-500',
    iconBg: 'bg-amber-100 text-amber-600',
  },
  {
    icon: Sparkles,
    question: 'لو عجبتك رواية…',
    action: 'كتب مشابهة حسب ذوقك',
    color: 'from-purple-500 to-violet-600',
    iconBg: 'bg-purple-100 text-purple-600',
  },
];

const HookCards = () => {
  const [sectionRef, isInView] = useInView<HTMLElement>({ threshold: 0.2 });

  return (
    <section
      ref={sectionRef}
      className={`py-10 transition-all duration-700 ${
        isInView ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-8'
      }`}
    >
      <div className="container mx-auto px-4">
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
          {hooks.map((hook, index) => (
            <button
              key={hook.question}
              className="group relative overflow-hidden rounded-2xl border border-border bg-card p-6 text-right transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
              style={{ animationDelay: `${index * 100}ms` }}
            >
              {/* Gradient overlay on hover */}
              <div className={`absolute inset-0 bg-gradient-to-br ${hook.color} opacity-0 transition-opacity duration-300 group-hover:opacity-5`} />
              
              <div className="relative">
                <div className={`mb-4 inline-flex rounded-xl p-3 ${hook.iconBg} transition-transform duration-300 group-hover:rotate-2`}>
                  <hook.icon className="h-6 w-6" />
                </div>
                <p className="mb-2 text-lg font-medium text-foreground">
                  {hook.question}
                </p>
                <p className={`bg-gradient-to-l ${hook.color} bg-clip-text font-semibold text-transparent`}>
                  {hook.action} ←
                </p>
              </div>
            </button>
          ))}
        </div>
      </div>
    </section>
  );
};

export default HookCards;
